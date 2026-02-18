<?php
/**
 * CYN Tourism — ExportController
 * Handles PDF generation, email sending, and WhatsApp sharing
 * 
 * NOTE: vendor/autoload.php is already loaded by index.php (the front controller).
 * Do NOT require it here — it causes a fatal error on shared hosting if the path
 * differs or vendor is not yet installed.
 */

use Dompdf\Dompdf;
use Dompdf\Options;

class ExportController extends Controller
{
    /**
     * Resolve partner logo path if partner has logo_on_vouchers enabled.
     * Looks up partner by partner_id or company_name.
     */
    private function resolvePartnerLogo(array $record): string
    {
        $partnerId = (int)($record['partner_id'] ?? $record['company_id'] ?? 0);
        $companyName = $record['company_name'] ?? '';
        $partner = null;

        if ($partnerId > 0) {
            $partner = Database::fetchOne("SELECT logo, logo_on_vouchers FROM partners WHERE id = ?", [$partnerId]);
        } elseif ($companyName) {
            $partner = Database::fetchOne("SELECT logo, logo_on_vouchers FROM partners WHERE company_name = ?", [$companyName]);
        }

        if ($partner && !empty($partner['logo_on_vouchers']) && !empty($partner['logo'])) {
            return $partner['logo'];
        }
        return '';
    }

    /**
     * Generate and stream Invoice PDF
     */
    public function invoicePdf(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);
        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $invoiceItems = Database::fetchAll(
            "SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC", [$id]
        );

        $html = $this->renderPdfTemplate('invoices/pdf', [
            'invoice'        => $invoice,
            'invoiceItems'   => $invoiceItems,
            'partnerLogo'    => $this->resolvePartnerLogo($invoice),
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'Invoice-' . $invoice['invoice_no'] . '.pdf';
        $this->streamPdf($html, $filename);
    }

    /**
     * Generate and stream Voucher PDF
     */
    public function voucherPdf(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);
        if (!$voucher) { header('Location: ' . url('vouchers')); exit; }

        $html = $this->renderPdfTemplate('vouchers/pdf', [
            'voucher'        => $voucher,
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'Voucher-' . $voucher['voucher_no'] . '.pdf';
        $this->streamPdf($html, $filename);
    }

    /**
     * Send document via email with PDF attachment
     */
    public function sendEmail(): void
    {
        $this->requireAuth();

        $type = $_POST['type'] ?? '';      // 'invoice' or 'voucher'
        $id   = (int)($_POST['id'] ?? 0);
        $to   = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid email address']);
            return;
        }

        // Generate PDF
        if ($type === 'invoice') {
            require_once ROOT_PATH . '/src/Models/Invoice.php';
            $record = Invoice::getById($id);
            if (!$record) { $this->jsonResponse(['success' => false, 'message' => 'Invoice not found']); return; }
            $emailItems = Database::fetchAll("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id ASC", [$id]);
            $html = $this->renderPdfTemplate('invoices/pdf', [
                'invoice' => $record, 'invoiceItems' => $emailItems, 'companyName' => COMPANY_NAME,
                'companyAddress' => COMPANY_ADDRESS, 'companyPhone' => COMPANY_PHONE, 'companyEmail' => COMPANY_EMAIL,
            ]);
            $filename = 'Invoice-' . $record['invoice_no'] . '.pdf';
            $subject = $subject ?: 'Invoice ' . $record['invoice_no'] . ' — ' . COMPANY_NAME;
        } else {
            require_once ROOT_PATH . '/src/Models/Voucher.php';
            $record = Voucher::getById($id);
            if (!$record) { $this->jsonResponse(['success' => false, 'message' => 'Voucher not found']); return; }
            $html = $this->renderPdfTemplate('vouchers/pdf', [
                'voucher' => $record, 'companyName' => COMPANY_NAME,
                'companyAddress' => COMPANY_ADDRESS, 'companyPhone' => COMPANY_PHONE, 'companyEmail' => COMPANY_EMAIL,
            ]);
            $filename = 'Voucher-' . $record['voucher_no'] . '.pdf';
            $subject = $subject ?: 'Voucher ' . $record['voucher_no'] . ' — ' . COMPANY_NAME;
        }

        $pdfContent = $this->generatePdfContent($html);

        // Build MIME email with attachment
        $boundary = md5(time());
        $headers  = "From: " . COMPANY_NAME . " <" . COMPANY_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . COMPANY_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= "<html><body><p>" . nl2br(htmlspecialchars($message ?: 'Please find the attached document.')) . "</p>";
        $body .= "<p>Best regards,<br>" . htmlspecialchars(COMPANY_NAME) . "</p></body></html>\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: application/pdf; name=\"{$filename}\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n\r\n";
        $body .= chunk_split(base64_encode($pdfContent)) . "\r\n";
        $body .= "--{$boundary}--";

        $sent = @mail($to, $subject, $body, $headers);

        $this->jsonResponse([
            'success' => $sent,
            'message' => $sent ? 'Email sent successfully!' : 'Failed to send email. Check server mail configuration.',
        ]);
    }

    /**
     * Generate WhatsApp share link
     */
    public function whatsappShare(): void
    {
        $this->requireAuth();

        $type  = $_GET['type'] ?? '';
        $id    = (int)($_GET['id'] ?? 0);
        $phone = preg_replace('/[^0-9]/', '', $_GET['phone'] ?? '');

        if ($type === 'invoice') {
            require_once ROOT_PATH . '/src/Models/Invoice.php';
            $record = Invoice::getById($id);
            $docNo = $record['invoice_no'] ?? 'N/A';
            $amount = number_format($record['total_amount'] ?? 0, 2) . ' ' . ($record['currency'] ?? 'USD');
            $text = "📄 *Invoice: {$docNo}*\n💰 Amount: {$amount}\n🏢 " . COMPANY_NAME . "\n📞 " . COMPANY_PHONE;
            $downloadUrl = url("invoices/pdf") . "?id={$id}";
        } else {
            require_once ROOT_PATH . '/src/Models/Voucher.php';
            $record = Voucher::getById($id);
            $docNo = $record['voucher_no'] ?? 'N/A';
            $text = "🎫 *Voucher: {$docNo}*\n📍 {$record['pickup_location']} ➜ {$record['dropoff_location']}\n📅 " . date('d/m/Y', strtotime($record['pickup_date'])) . " {$record['pickup_time']}\n👥 {$record['total_pax']} Pax\n🏢 " . COMPANY_NAME;
            $downloadUrl = url("vouchers/pdf") . "?id={$id}";
        }

        $text .= "\n\n📥 Download PDF: {$downloadUrl}";

        $waUrl = 'https://wa.me/' . ($phone ?: '') . '?text=' . urlencode($text);
        header('Location: ' . $waUrl);
        exit;
    }

    // ---- Private helpers ----

    /**
     * Generate and stream Hotel Voucher PDF
     */
    public function hotelVoucherPdf(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM hotel_vouchers WHERE id = ?");
        $stmt->execute([$id]);
        $voucher = $stmt->fetch();
        if (!$voucher) { header('Location: ' . url('hotel-voucher')); exit; }

        $guestProgram = HotelController::resolveGuestProgram($id);

        $html = $this->renderPdfTemplate('hotels/voucher_pdf', [
            'voucher'        => $voucher,
            'guestProgram'   => $guestProgram,
            'partnerLogo'    => $this->resolvePartnerLogo($voucher),
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'HotelVoucher-' . $voucher['voucher_no'] . '.pdf';
        $this->streamPdf($html, $filename);
    }

    /**
     * Generate and stream Tour Voucher PDF
     */
    public function tourVoucherPdf(): void
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$id]);
        $tour = $stmt->fetch();
        if (!$tour) { header('Location: ' . url('tour-voucher')); exit; }

        $html = $this->renderPdfTemplate('tours/voucher_pdf', [
            'tour'           => $tour,
            'partnerLogo'    => $this->resolvePartnerLogo($tour),
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'TourVoucher-' . ($tour['tour_code'] ?? $tour['id']) . '.pdf';
        $this->streamPdf($html, $filename);
    }

    /**
     * Generate and stream Transfer Voucher PDF
     */
    public function transferVoucherPdf(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Voucher.php';

        $id = (int)($_GET['id'] ?? 0);
        $voucher = Voucher::getById($id);
        if (!$voucher) { header('Location: ' . url('transfers')); exit; }

        $html = $this->renderPdfTemplate('transfers/pdf', [
            'voucher'        => $voucher,
            'partnerLogo'    => $this->resolvePartnerLogo($voucher),
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'TransferVoucher-' . ($voucher['voucher_no'] ?? $voucher['id']) . '.pdf';
        $this->streamPdf($html, $filename);
    }

    /**
     * Generate and stream Receipt PDF
     */
    public function receiptPdf(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $receipt = Invoice::getById($id);
        if (!$receipt || $receipt['status'] !== 'paid') {
            header('Location: ' . url('receipts'));
            exit;
        }

        $html = $this->renderPdfTemplate('receipts/pdf', [
            'receipt'        => $receipt,
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'Receipt-' . $receipt['invoice_no'] . '.pdf';
        $this->streamPdf($html, $filename);
    }

    private function renderPdfTemplate(string $template, array $data): string
    {
        $basePath = App::getBasePath();
        $viewFile = $basePath . '/views/' . $template . '.php';
        extract($data);
        ob_start();
        require $viewFile;
        return ob_get_clean();
    }

    private function generatePdfContent(string $html): string
    {
        $dompdf = $this->createDompdf();
        $html = $this->wrapArabicSupport($html);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function streamPdf(string $html, string $filename): void
    {
        $dompdf = $this->createDompdf();
        $html = $this->wrapArabicSupport($html);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // If download=1 in query, force download; otherwise display inline
        $attachment = isset($_GET['download']) ? 'attachment' : 'inline';
        $dompdf->stream($filename, ['Attachment' => ($attachment === 'attachment')]);
        exit;
    }

    private function createDompdf(): Dompdf
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);

        // Allow custom fonts directory
        $fontDir = APP_ROOT . '/assets/fonts';
        if (is_dir($fontDir)) {
            $options->set('fontDir', $fontDir);
            $options->set('fontCache', APP_ROOT . '/storage/fonts');
            if (!is_dir(APP_ROOT . '/storage/fonts')) {
                @mkdir(APP_ROOT . '/storage/fonts', 0755, true);
            }
        }

        return new Dompdf($options);
    }

    private function wrapArabicSupport(string $html): string
    {
        $currentLang = $_SESSION['language'] ?? 'en';
        $isRtl = ($currentLang === 'ar');

        if (!$isRtl) {
            return $html;
        }

        // Inject RTL CSS and Arabic font-family at the top of <style> or before </head>
        $rtlCss = '
        <style>
            body, td, th, p, div, span {
                font-family: "DejaVu Sans", "Amiri", "Noto Sans Arabic", sans-serif;
                direction: rtl;
                text-align: right;
            }
            table { direction: rtl; }
        </style>';

        // Add dir="rtl" to html tag if present
        $html = preg_replace('/<html([^>]*)>/', '<html$1 dir="rtl">', $html, 1);

        // Inject RTL styles before </head> or at the start
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $rtlCss . '</head>', $html);
        } else {
            $html = $rtlCss . $html;
        }

        return $html;
    }

    /**
     * ICS Calendar Export — export a transfer, tour, or hotel booking as .ics
     */
    public function icsExport(): void
    {
        $this->requireAuth();

        $type = $_GET['type'] ?? '';
        $id   = (int)($_GET['id'] ?? 0);

        switch ($type) {
            case 'transfer':
                require_once ROOT_PATH . '/src/Models/Voucher.php';
                $v = Voucher::getById($id);
                if (!$v) { http_response_code(404); exit('Not found'); }
                $ics = generate_ics(
                    'Transfer: ' . ($v['pickup_location'] ?? '') . ' → ' . ($v['dropoff_location'] ?? ''),
                    $v['pickup_date'] ?? date('Y-m-d'),
                    $v['pickup_time'] ?? '',
                    $v['pickup_date'] ?? '',
                    '',
                    $v['pickup_location'] ?? '',
                    'Guest: ' . ($v['guest_name'] ?? '') . ' | Pax: ' . ($v['total_pax'] ?? 0) . ' | Flight: ' . ($v['flight_no'] ?? '-')
                );
                stream_ics($ics, 'transfer-' . ($v['voucher_no'] ?? $id) . '.ics');
                break;

            case 'tour':
                $t = Database::fetchOne("SELECT * FROM tours WHERE id = ?", [$id]);
                if (!$t) { http_response_code(404); exit('Not found'); }
                $ics = generate_ics(
                    'Tour: ' . ($t['tour_name'] ?? ''),
                    $t['tour_date'] ?? date('Y-m-d'),
                    $t['pickup_time'] ?? '',
                    $t['tour_date'] ?? '',
                    '',
                    $t['pickup_location'] ?? '',
                    'Guest: ' . ($t['guest_name'] ?? '') . ' | Pax: ' . ($t['total_pax'] ?? 0)
                );
                stream_ics($ics, 'tour-' . ($t['tour_code'] ?? $id) . '.ics');
                break;

            case 'hotel':
                $h = Database::fetchOne("SELECT * FROM hotel_vouchers WHERE id = ?", [$id]);
                if (!$h) { http_response_code(404); exit('Not found'); }
                $ics = generate_ics(
                    'Hotel: ' . ($h['hotel_name'] ?? '') . ' — ' . ($h['guest_name'] ?? ''),
                    $h['check_in'] ?? date('Y-m-d'),
                    '',
                    $h['check_out'] ?? '',
                    '',
                    $h['hotel_name'] ?? '',
                    'Room: ' . ($h['room_type'] ?? '') . ' | Board: ' . ($h['board_type'] ?? 'BB') . ' | Nights: ' . ($h['nights'] ?? 0)
                );
                stream_ics($ics, 'hotel-' . ($h['voucher_no'] ?? $id) . '.ics');
                break;

            default:
                http_response_code(400);
                exit('Invalid type. Use: transfer, tour, hotel');
        }
    }
}
