<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد مشفر</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-box {
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
            background-color: #667eea;
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
        .link { margin-top: 15px; display: block; color: #667eea; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="register-box">
    <h2>إنشاء حساب مشفر 🛡️</h2>
    <form method="POST" action="">
        <input type="text" name="new_username" placeholder="اختر اسم المستخدم" required>
        <input type="password" name="new_password" placeholder="اختر كلمة المرور" required>
        <button type="submit">تسجيل الحساب الأمني</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user = $_POST['new_username'];
        $pass = $_POST['new_password'];

        // تشفير كلمة المرور بأسلوب محترف ومحمي عالمياً
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        $host = 'localhost';
        $db_name = 'my_website_db';
        $db_user = 'root';
        $db_pass = ''; 

        try {
            $connect = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $check = $connect->prepare("SELECT * FROM users WHERE username = :username");
            $check->execute([':username' => $user]);

            if ($check->rowCount() > 0) {
                echo "<div class='message' style='background-color: #f8d7da; color: #721c24;'>عذراً: اسم المستخدم محجوز! ❌</div>";
            } else {
                // إدخال كلمة المرور المشفرة $hashed_password بدلاً من النص الصريح
                $statement = $connect->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                $statement->execute([
                    ':username' => $user,
                    ':password' => $hashed_password
                ]);
                echo "<div class='message' style='background-color: #d4edda; color: #155724;'>تم تسجيل الحساب مشفراً بنجاح! 🔒</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='message' style='color:red;'>فشل الاتصال: " . $e->getMessage() . "</div>";
        }
    }
    ?>
    <a href="login.php" class="link">لديك حساب؟ سجل دخولك من هنا</a>
</div>

</body>
</html>
