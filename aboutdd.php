<?php
require_once 'config.php';

// تعريف المتغيرات الأساسية
 $pageTitle = "من نحن | PSGOLD4GAMER";

// دالة لعرض رسالة النموذج
function showFormMessage($message, $type = 'info') {
    $message = htmlspecialchars($message);
    $type = htmlspecialchars($type);
    echo "<div class='form-message $type'>$message</div>";
}

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '';
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        showFormMessage('يرجى ملء جميع الحقول المطلوبة', 'error');
    } else {
        // هنا يمكنك إضافة كود لإرسال البريد الإلكتروني
        // مثل: mail($email, $subject, $message, "From: $name <$email>");
        showFormMessage('جاري إرسال رسالتك...', 'info');
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="من نحن - PSGOLD4GAMER">
    <link rel="canonical" href="https://PKGStation.com/about.php">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="صفحة" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/about.css">
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">PSGOLD4GAMER</a>
                <div class="platform-nav">
                    <a href="index.php" class="platform-nav-btn">
                        <i class="fas fa-home"></i> الرئيسية
                    </a>
                    <a href="ps4-games.php" class="platform-nav-btn ps4">
                        <i class="fab fa-playstation"></i> PS4
                    </a>
                    <a href="ps5-games.php" class="platform-nav-btn ps5">
                        <i class="fab fa-playstation"></i> PS5
                    </a>
                    <a href="ps3-games.php" class="platform-nav-btn ps3">
                        <i class="fab fa-playstation"></i> PS3
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== PAGE HERO ===== -->
    <main class="page-hero">
        <div class="container">
            <h1>من نحن</h1>
            <p>موقعنا هو الساحة المثالية لعشاق ألعاب بلايستيشن في جميع أنحاء العالم العربي.</p>
        </div>
    </main>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content container">
        <section class="about-content">
            <section class="about-section">
                <h2>قصتنا</h2>
                <p>بدأنا كفريق صغير من عشاق ألعاب بلايستيشن، نهدفنا بسيط هو توفير أفضل تجربة ممكنة للاعبين العرب.</p>
                <p>نؤمن بكل جوانبنا أن نستطيع أن نقدم محتوى عالي الجودة، وسهل نعمل بجد ونشاط لتحسين موقعنا باستمرار الوقت.</p>
            </section>

            <section class="about-section">
                <h2>مهمتنا</h2>
                <p>نحن فريق متخصص في مجال الألعاب الإلكترونيكية، من تطوير وتغطية أخبار الألعاب المختلفة.</p>
                <p>نؤمن بتقديم معلومات دقيقة وموثوقة حول الألعاب، وتزويد دعم فني للاعبين.</p>
            </section>

            <section class="about-section">
                <h2>رؤيتنا</h2>
                <p>نسعى أن نكون المصدر الأول والأكثر موثوقية لمعلومات الألعاب في العالم العربي.</p>
                <p>نهدفنا هو أن نكون المصدر الأول والموثوق للمعلومات حول الألعاب، وأن نقدم محتوى شامل يغطي جميع جوانب اللعب.</p>
            </section>

            <section class="about-section">
                <h2>اتصل بنا</h2>
                <p>نرحب بكل استفساراتكم واقتراحاتكم بكل سرور واهتمام.</p>
                <p>سنعمل بكل سرور واهتمام بسرعة واهتمام.</p>
                
                <!-- نموذج اتصل بنا -->
                <form id="contactForm" method="post" action="about.php">
                    <div class="form-group">
                        <label for="name">الاسمك</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">البريدك الإلكتروني</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">الموضوع</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">الرسالتك</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submit" class="submit-btn">
                            <i class="scroll-to-top fas fa-paper-plane"></i>
                            <span>إرسال</span>
                        </button>
                    </div>
                </form>
                
                <!-- رسالة النموذج -->
                <div id="formMessage"></div>
            </section>
        </main>

     <?php require_once 'includes/footer.php'; ?>

    <!-- بيانات المنظمة (Organization) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Crganization",
        "name": "PSGOLD4GAMER",
        "url": "https://PKGStation.com",
        "logo": "https://PKGStation.com/images/logo.png",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "sameAs": [
            "https://www.youtube.com/PKGStation",
            "https://www.facebook.com/PKGStation",
            "https://t.me/PKGStation"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": "Arabic"
        }
    }
    </script>
    
    <!-- بيانات الموقع (Website) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "PSGOLD4GAMER",
        "url": "https://PKGStation.com",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://PKGStation.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <!-- JavaScript -->
    <script src="assets/js/about.js"></script>
</body>
</html>