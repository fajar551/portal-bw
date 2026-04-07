<!DOCTYPE html>
<html>
<head>
    <title>Invoice Reminder</title>
</head>
<body>
    <p>Dear {{ $invoice->firstname }} {{ $invoice->lastname }},</p>
    <p>This is a reminder that your invoice #{{ $invoice->invoice_id }} is due on {{ $invoice->duedate }}.</p>
    <p>Thank you!</p>
</body>
</html>