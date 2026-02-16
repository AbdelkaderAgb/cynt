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
     * Generate and stream Invoice PDF
     */
    public function invoicePdf(): void
    {
        $this->requireAuth();
        require_once ROOT_PATH . '/src/Models/Invoice.php';

        $id = (int)($_GET['id'] ?? 0);
        $invoice = Invoice::getById($id);
        if (!$invoice) { header('Location: ' . url('invoices')); exit; }

        $html = $this->renderPdfTemplate('invoices/pdf', [
            'invoice'        => $invoice,
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
            $html = $this->renderPdfTemplate('invoices/pdf', [
                'invoice' => $record, 'companyName' => COMPANY_NAME,
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
            'companyName'    => COMPANY_NAME,
            'companyAddress' => COMPANY_ADDRESS,
            'companyPhone'   => COMPANY_PHONE,
            'companyEmail'   => COMPANY_EMAIL,
        ]);

        $filename = 'TourVoucher-' . ($tour['tour_code'] ?? $tour['id']) . '.pdf';
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
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function streamPdf(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // If download=1 in query, force download; otherwise display inline
        $attachment = isset($_GET['download']) ? 'attachment' : 'inline';
        $dompdf->stream($filename, ['Attachment' => ($attachment === 'attachment')]);
        exit;
    }

}
