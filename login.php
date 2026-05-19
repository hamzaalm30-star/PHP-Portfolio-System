<?php
session_start(); // بدء الجلسة لتذكر المستخدم
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول آمن</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #11998e, #38ef7d);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            text-align: center;
            width: 320px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #eee;
            border-radius: 6px;
            box-sizing: border-box;
            outline: none;
        }
        button {
            background-color: #11998e;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .message { margin-top: 20px; padding: 10px; border-radius: 6px; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>تسجيل الدخول الآمن 🔐</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="اسم المستخدم" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <button type="submit">دخول</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user = $_POST['username'];
        $pass = $_POST['password'];

        $host = 'localhost';
        $db_name = 'my_website_db';
        $db_user = 'root';
        $db_pass = ''; 

        try {
            $connect = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // نبحث فقط عن اسم المستخدم أولاً
            $statement = $connect->prepare("SELECT * FROM users WHERE username = :username");
            $statement->execute([':username' => $user]);
            $userData = $statement->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                // مطابقة كلمة المرور المدخلة مع التشفير المحفوظ في قاعدة البيانات
                if (password_verify($pass, $userData['password'])) {
                    
                    // حفظ اسم المستخدم في الجلسة
                    $_SESSION['user'] = $userData['username'];
                    
                    // توجيهه فوراً إلى لوحة التحكم الداخلية المحمية
                    header("Location: dashboard.php");
                    exit();
                    
                } else {
                    echo "<div class='message' style='background-color: #f8d7da; color: #721c24;'>كلمة المرور غير صحيحة! ❌</div>";
                }
            } else {
                echo "<div class='message' style='background-color: #f8d7da; color: #721c24;'>الحساب غير موجود! ❌</div>";
            }

        } catch (PDOException $e) {
            echo "<div class='message' style='color:red;'>فشل الاتصال: " . $e->getMessage() . "</div>";
        }
    }
    ?>
    <a href="register.php" style="margin-top: 15px; display: block; color: #11998e; text-decoration: none; font-size: 14px;">ليس لديك حساب؟ أنشئ حساباً جديداً من هنا</a>
</div>

</body>
</html>
