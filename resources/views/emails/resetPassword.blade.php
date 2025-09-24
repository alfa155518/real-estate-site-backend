<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
</head>
<body>
    <p>مرحبًا</p>
    <p>لقد تلقينا طلبًا لإعادة تعيين كلمة المرور الخاصة بك. يرجى النقر على الرابط التالي لإعادة تعيين كلمة المرور:</p>
    @if(isset($resetLink))
    <a href="{{ $resetLink }}">إعادة تعيين كلمة المرور</a>
    @endif
    <p>إذا لم تطلب إعادة تعيين، يمكنك تجاهل هذا البريد.</p>
    <p>شكراً لاستخدامكم موقعنا</p>
</body>
</html>
