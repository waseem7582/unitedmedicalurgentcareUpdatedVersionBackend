<!DOCTYPE html>
<html>
  <head>
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
      @page {
        margin: 5px;
      }
      @font-face {
        font-family: "Inter", sans-serif;
        font-style: normal;
        font-weight: 400;
      }
      body {
        font-family: "Inter", sans-serif;
        margin: 0;
        padding: 0;
        background-color: #ffffff;
      }
      hr {
        border-color: #000;
        background-color: #000;
      }
      .container {
        padding: 20px;
        padding-top: 0;
        width: 90%;
        margin: auto;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-top: 0px;
      }
      .header {
        text-align: center;
        margin-bottom: 20px;
      }
      .header .headerMain {
        display: flex;
        align-items: start;
        justify-content: space-between;
      }
      .header .hospital-name {
        font-size: 24px;
        color: #333;
        font-weight: bold;
      }
      .header .hospital-logo {
        width: 50px;
        height: auto;
        margin-bottom: 10px;
      }
      .hospital-contact {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
      }
      .invoice-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
        text-align: center;
      }
      .details p {
        margin: 0;
        margin-bottom: 5px;
        font-size: 13px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      .total {
        font-size: 14px;
        font-weight: bold;
        text-align: right;
        padding: 10px;
        border-top: 1px solid #000;
      }
      .footer {
        text-align: left;
        font-size: 12px;
        color: #666;
        margin-top: 10px;
      }
      .footer p {
        margin: 0;
        margin-bottom: 4px;
      }
      .additional-info {
        text-align: center;
        font-size: 12px;
        color: #666;
        margin-top: 50px;
      }
      .additional-info p {
        margin: 0;
      }
      .paymentStatus {
        font-size: 14px;
        font-weight: bold;
        color: #333;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <!-- Header Section -->
      <div class="header">
        <table style="width: 100%; border: 0">
          <tr>
            <!-- Hospital Logo and Name Section -->
            <td style="vertical-align: top; width: 50%; border: 0; padding: 0">
              <div style="text-align: left">
                <img
                  src="{{ public_path('storage/' . $invoice->logo) }}"
                  alt="Hospital Logo"
                  class="hospital-logo"
                  style="width: 50px; height: auto; margin-bottom: 10px"
                />
                <h1 style="font-size: 24px; color: #333; margin: 0">
                  {{ $invoice->clinic_name }}
                </h1>
                <p style="font-size: 12px; color: #666; margin-top: 5px; margin-bottom: 0;">
                  {{ $invoice->address }}<br />
                  Phone: {{ $invoice->phone }} , {{ $invoice->phone_second }} <br />
                  Email: {{ $invoice->email }}
                </p>
              </div>
            </td>

            <!-- Invoice Title Section -->
            <td style="vertical-align: bottom; text-align: right; width: 50%; border: 0; padding: 0;">
              <h1 style="font-size: 40px; color: #333; margin: 0; padding: 0">INVOICE</h1>
            </td>
          </tr>
        </table>
      </div>
      <hr />

      <!-- Invoice Details -->
      <table style="width: 100%; border-collapse: collapse; border: 0; margin: 30px 0;">
        <tr>
          <td style="vertical-align: top; width: 50%; border: 0; padding: 0">
            <div class="details">
              <p><strong>Invoice No :</strong> #{{ $invoice->id }}</p>
            </div>
            <div class="details">
              <p><strong>Date:</strong> {{ $invoice->invoice_date }}</p>
              <p><strong>Appointment ID:</strong> {{ $invoice->appointment_id }}</p>
            </div>
          </td>

          <!-- Invoice to -->
          <td style="vertical-align: top; text-align: right; width: 50%; border: 0; padding: 0;">
            <p style="color: #333; margin: 0; padding: 0">Invoice To :</p>
            <h1 style="font-size: 20px; color: #000; margin: 0; padding: 0; text-transform: uppercase; margin-top: 10px;">
              @if(isset($invoice->patient_f_name) && isset($invoice->patient_l_name))
                {{ $invoice->patient_f_name }} {{ $invoice->patient_l_name }}
              @else
                {{ $invoice->user_f_name ?? '' }} {{ $invoice->user_l_name ?? '' }}
              @endif
            </h1>
            <p style="color: #333; margin: 0; padding: 0; margin-bottom: 5px">
              @if(isset($invoice->patient_phone)) {{ $invoice->patient_phone }} @else {{ $invoice->user_phone ?? '' }} @endif
            </p>

            @if(isset($invoice->patient_id))
            <div class="details">
              <p>Patient ID: {{ $invoice->patient_id }}</p>
            </div>
            @endif
          </td>
        </tr>
      </table>

      <!-- Items Table -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
          <tr>
            <th style="font-size: 14px; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px; text-align: left;">Description</th>
            <th style="font-size: 14px; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px; text-align: left;">Quantity</th>
            <th style="font-size: 14px; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px; text-align: left;">Price</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($invoice->items as $item)
          <tr>
            <td style="border: none; padding: 8px; font-size: 14px">{{ $item->description }}</td>
            <td style="border: none; padding: 8px; font-size: 14px">{{ $item->quantity }}</td>
            <td style="border: none; padding: 8px; font-size: 14px">{{ number_format($item->total_price, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <!-- Coupon Details -->
   

      <!-- Total Amount -->
      <div class="total">

      @if(isset($invoice->coupon_title))
          <p style="font-weight: 400; margin-bottom:2px; margin-top:2px">Applied Coupon: {{ $invoice->coupon_title }}</p>
          <p style="font-weight: 400; margin-bottom:2px; margin-top:2px">Discount Amount: {{ number_format($invoice->coupon_off_amount, 2) }}</p>
        </tr>
      @endif

      <p style="font-weight: 400; margin-bottom:2px; margin-top:2px">Total Amount: {{ number_format($invoice->total_amount ,2) }}</p>

      </div>

      <!-- Payment Status and Created Date -->
      <div class="footer">
        <p class="paymentStatus">Payment Status: {{ $invoice->status }}</p>
        <p>Created At: {{ $invoice->created_at }}</p>
      </div>

      <!-- Additional Information -->
      <div class="additional-info">
        <p>This is a computer-generated invoice and does not require a signature.</p>
        <p>Thank you for your payment!</p>
      </div>
    </div>
  </body>
</html>
