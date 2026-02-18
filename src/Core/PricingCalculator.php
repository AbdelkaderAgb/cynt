<?php
/**
 * CYN Tourism — PricingCalculator
 * Centralized pricing calculation engine for all service types.
 * Used server-side for invoice totals and price validation.
 */
class PricingCalculator
{
    /**
     * Calculate tour pricing based on adult/child/infant counts and rates.
     *
     * @param float $priceAdult   Price per adult
     * @param float $priceChild   Price per child
     * @param float $priceInfant  Price per infant
     * @param int   $adults       Number of adults
     * @param int   $children     Number of children
     * @param int   $infants      Number of infants
     * @return float Total tour price
     */
    public static function calculateTour(
        float $priceAdult,
        float $priceChild,
        float $priceInfant,
        int $adults,
        int $children,
        int $infants
    ): float {
        return ($adults * $priceAdult) + ($children * $priceChild) + ($infants * $priceInfant);
    }

    /**
     * Calculate hotel pricing based on room rate, nights, and rooms.
     *
     * @param float      $roomRate         Nightly room rate
     * @param int        $nights           Number of nights
     * @param int        $rooms            Number of rooms
     * @param float|null $seasonMultiplier  Seasonal pricing multiplier (default 1.0)
     * @return float Total hotel price
     */
    public static function calculateHotel(
        float $roomRate,
        int $nights,
        int $rooms,
        ?float $seasonMultiplier = 1.0
    ): float {
        $multiplier = $seasonMultiplier ?: 1.0;
        return $roomRate * $nights * $rooms * $multiplier;
    }

    /**
     * Calculate transfer pricing based on unit type.
     *
     * @param float  $basePrice  Base price for the transfer
     * @param string $unit       Pricing unit: per_vehicle, per_person, per_group, flat
     * @param int    $pax        Number of passengers (used for per_person)
     * @return float Total transfer price
     */
    public static function calculateTransfer(
        float $basePrice,
        string $unit,
        int $pax
    ): float {
        return match ($unit) {
            'per_person' => $basePrice * $pax,
            'per_vehicle', 'per_group', 'flat' => $basePrice,
            default => $basePrice,
        };
    }

    /**
     * Calculate a single line item total based on service type and parameters.
     *
     * @param array $item Line item data with keys:
     *   - unit_type: per_person|per_night|per_vehicle|per_group|flat
     *   - unit_price: base unit price
     *   - quantity: quantity multiplier
     *   - adults, children_count, infants: pax counts (for per_person tours)
     *   - price_child, price_infant: child/infant rates (optional)
     *   - nights: number of nights (for per_night)
     *   - season_multiplier: seasonal adjustment (optional)
     * @return float Calculated line total
     */
    public static function calculateLineItem(array $item): float
    {
        $unitPrice = (float)($item['unit_price'] ?? 0);
        $quantity  = max(1, (int)($item['quantity'] ?? 1));
        $unitType  = $item['unit_type'] ?? 'flat';

        switch ($unitType) {
            case 'per_person':
                $adults   = (int)($item['adults'] ?? $quantity);
                $children = (int)($item['children_count'] ?? 0);
                $infants  = (int)($item['infants'] ?? 0);
                $priceChild  = (float)($item['price_child'] ?? $unitPrice);
                $priceInfant = (float)($item['price_infant'] ?? 0);
                return self::calculateTour($unitPrice, $priceChild, $priceInfant, $adults, $children, $infants);

            case 'per_night':
                $nights = max(1, (int)($item['nights'] ?? $quantity));
                $rooms  = max(1, $quantity);
                $seasonMultiplier = (float)($item['season_multiplier'] ?? 1.0);
                return self::calculateHotel($unitPrice, $nights, $rooms, $seasonMultiplier);

            case 'per_vehicle':
            case 'per_group':
                return $unitPrice * $quantity;

            case 'flat':
            default:
                return $unitPrice * $quantity;
        }
    }

    /**
     * Calculate invoice totals from an array of line items.
     *
     * @param array $lineItems  Array of line items, each with unit_price and quantity at minimum
     * @param float $taxRate    Tax rate as percentage (e.g. 18 for 18%)
     * @param float $discount   Discount amount (absolute, not percentage)
     * @return array ['subtotal' => float, 'tax_amount' => float, 'discount' => float, 'total' => float]
     */
    public static function calculateInvoiceTotals(
        array $lineItems,
        float $taxRate = 0,
        float $discount = 0
    ): array {
        $subtotal = 0;

        foreach ($lineItems as $item) {
            if (isset($item['total_price']) && (float)$item['total_price'] > 0) {
                $subtotal += (float)$item['total_price'];
            } else {
                $subtotal += self::calculateLineItem($item);
            }
        }

        $taxAmount = $taxRate > 0 ? round($subtotal * ($taxRate / 100), 2) : 0;
        $total = round($subtotal + $taxAmount - $discount, 2);

        return [
            'subtotal'   => round($subtotal, 2),
            'tax_amount' => $taxAmount,
            'discount'   => round($discount, 2),
            'total'      => max(0, $total),
        ];
    }

    /**
     * Compute the number of nights between two dates.
     *
     * @param string $checkIn  Check-in date (Y-m-d)
     * @param string $checkOut Check-out date (Y-m-d)
     * @return int Number of nights (minimum 1)
     */
    public static function computeNights(string $checkIn, string $checkOut): int
    {
        $in  = new \DateTime($checkIn);
        $out = new \DateTime($checkOut);
        $diff = $in->diff($out)->days;
        return max(1, $diff);
    }
}
