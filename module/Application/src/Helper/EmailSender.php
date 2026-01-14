<?php

declare(strict_types=1);

namespace Application\Helper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class EmailSender
{
    /**
     * Send order confirmation email via Brevo (Sendinblue) API
     * Much faster than SMTP - no connection timeout issues
     * 
     * @param array $customer Customer data (email, name)
     * @param int $orderId Order ID
     * @param array $items Order items
     * @param float $total Total amount
     * @return bool Success status
     */
    public static function sendOrderConfirmation(array $customer, int $orderId, array $items, float $total): bool
    {
        try {
            $apiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY');
            
            if (!$apiKey) {
                error_log("Brevo API key not configured");
                return false;
            }
            
            $client = new Client([
                'base_uri' => 'https://api.brevo.com/v3/',
                'timeout' => 3, // 3 second timeout
            ]);
            
            $emailData = [
                'sender' => [
                    'name' => 'Highlands Coffee',
                    'email' => 'noreply@brevo-mail.com', // Use Brevo default sender - always works
                ],
                'to' => [
                    [
                        'email' => $customer['email'],
                        'name' => $customer['name'] ?? 'Customer',
                    ]
                ],
                'replyTo' => [
                    'email' => $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: 'willbe.2002@outlook.com',
                    'name' => $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'Highlands Coffee',
                ],
                'subject' => "Order Confirmation - Highlands Coffee #{$orderId}",
                'htmlContent' => self::buildOrderEmailHtml($customer, $orderId, $items, $total),
            ];
            
            $response = $client->post('smtp/email', [
                'headers' => [
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $emailData,
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode === 201) {
                return true;
            } else {
                error_log("Brevo API unexpected status: {$statusCode}");
                return false;
            }
            
        } catch (GuzzleException $e) {
            error_log("Brevo API Error: " . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build HTML email template
     */
    private static function buildOrderEmailHtml(array $customer, int $orderId, array $items, float $total): string
    {
        $customerName = htmlspecialchars($customer['name'] ?? ($customer['first_name'] . ' ' . $customer['last_name']));
        $itemsHtml = '';
        
        foreach ($items as $item) {
            $itemName = htmlspecialchars($item['name']);
            $itemQty = (int)$item['quantity'];
            $itemPrice = number_format((float)$item['price'], 0, '.', ',');
            $itemTotal = number_format((float)$item['price'] * $itemQty, 0, '.', ',');
            
            $itemsHtml .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$itemName}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$itemQty}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>{$itemPrice} VND</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold;'>{$itemTotal} VND</td>
                </tr>
            ";
        }
        
        $totalFormatted = number_format($total, 0, '.', ',');
        
        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
    <div style='background: linear-gradient(135deg, #b2292e 0%, #8b1f23 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
        <h1 style='color: white; margin: 0; font-size: 28px;'>Highlands Coffee</h1>
        <p style='color: white; margin: 10px 0 0 0; font-size: 16px;'>Order Confirmation</p>
    </div>
    
    <div style='background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;'>
        <h2 style='color: #b2292e; margin-top: 0;'>Thank you for your order, {$customerName}!</h2>
        
        <p style='font-size: 16px;'>Your order has been received and is being processed.</p>
        
        <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <p style='margin: 0 0 10px 0;'><strong>Order ID:</strong> #{$orderId}</p>
            <p style='margin: 0 0 10px 0;'><strong>Status:</strong> <span style='color: #ff9800;'>Processing</span></p>
            <p style='margin: 0;'><strong>Email:</strong> {$customer['email']}</p>
        </div>
        
        <h3 style='color: #333; border-bottom: 2px solid #b2292e; padding-bottom: 10px;'>Order Details</h3>
        
        <table style='width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden;'>
            <thead>
                <tr style='background: #b2292e; color: white;'>
                    <th style='padding: 12px; text-align: left;'>Product</th>
                    <th style='padding: 12px; text-align: center;'>Quantity</th>
                    <th style='padding: 12px; text-align: right;'>Price</th>
                    <th style='padding: 12px; text-align: right;'>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
            <tfoot>
                <tr style='background: #f5f5f5;'>
                    <td colspan='3' style='padding: 15px; text-align: right; font-weight: bold; font-size: 18px;'>Total:</td>
                    <td style='padding: 15px; text-align: right; font-weight: bold; font-size: 18px; color: #b2292e;'>{$totalFormatted} VND</td>
                </tr>
            </tfoot>
        </table>
        
        <div style='margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;'>
            <p style='margin: 0; font-size: 14px;'><strong>Note:</strong> You will receive another email when your order is ready for pickup or delivery.</p>
        </div>
        
        <div style='margin-top: 30px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd;'>
            <p style='color: #666; font-size: 14px; margin: 5px 0;'>Highlands Coffee</p>
            <p style='color: #666; font-size: 14px; margin: 5px 0;'>Bringing you the best Vietnamese coffee since 1998</p>
            <p style='color: #999; font-size: 12px; margin: 15px 0 0 0;'>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
        ";
    }
    
    /**
     * Build plain text email for order confirmation (fallback)
     */
    private static function buildOrderEmailText(array $customer, int $orderId, array $items, float $total): string
    {
        $customerName = $customer['name'] ?? $customer['first_name'] ?? 'Customer';
        $text = "HIGHLANDS COFFEE - ORDER CONFIRMATION\n\n";
        $text .= "Dear {$customerName},\n\n";
        $text .= "Thank you for your order! Your order #{$orderId} has been confirmed.\n\n";
        $text .= "ORDER DETAILS:\n";
        $text .= str_repeat("-", 50) . "\n";
        
        foreach ($items as $item) {
            $text .= $item['name'] . "\n";
            $text .= "  Quantity: {$item['quantity']} x " . number_format($item['price'], 0, ',', '.') . " VND\n";
            $text .= "  Subtotal: " . number_format($item['quantity'] * $item['price'], 0, ',', '.') . " VND\n\n";
        }
        
        $text .= str_repeat("-", 50) . "\n";
        $text .= "TOTAL: " . number_format($total, 0, ',', '.') . " VND\n\n";
        
        $text .= "DELIVERY INFORMATION:\n";
        $text .= "Name: {$customerName}\n";
        $text .= "Phone: {$customer['phone']}\n";
        $text .= "Address: {$customer['address']}\n\n";
        
        $text .= "We will contact you shortly to confirm delivery details.\n\n";
        $text .= "Best regards,\n";
        $text .= "Highlands Coffee Team";
        
        return $text;
    }
}
