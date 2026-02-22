<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root . '/template/master-site';
$target = $root . '/template/master-site-tr';

if (!is_dir($source)) {
    fwrite(STDERR, "Source not found: {$source}\n");
    exit(1);
}

function rrmdir(string $dir): void {
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($dir);
}

function rcopy(string $src, string $dst): void {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            rcopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

rrmdir($target);
rcopy($source, $target);

$map = [
    'Invena Business Consulting HTML Template' => 'Kurumsal Isletme Web Sitesi',
    'Invena – A modern and responsive HTML template for consulting businesses. Perfect for finance, corporate, and agency websites. SEO-friendly, fast-loading, and easy to customize. Create a professional online presence today!' => 'Danismanlik isletmeleri icin modern ve mobil uyumlu kurumsal web temasi. Hizli, SEO uyumlu ve kolay duzenlenebilir.',
    'Company news' => 'Sirket Haberleri',
    'Faq' => 'SSS',
    'FAQ' => 'SSS',
    'Contact' => 'Iletisim',
    'About' => 'Hakkimizda',
    'About Us' => 'Hakkimizda',
    'Services' => 'Hizmetler',
    'Projects' => 'Projeler',
    'Team Member' => 'Ekip Uyeleri',
    'Latest Blog' => 'Son Yazilar',
    'Blog' => 'Blog',
    'Business One' => 'Isletme Bir',
    'Business Two' => 'Isletme Iki',
    'Business Three' => 'Isletme Uc',
    'Business Four' => 'Isletme Dort',
    'Business Website' => 'Kurumsal Web Sitesi',
    'Business agency' => 'Isletme Ajansi',
    'Business Management' => 'Isletme Yonetimi',
    'Finance Demo' => 'Finans Demolari',
    'Marketing agency' => 'Pazarlama Ajansi',
    'Insurance Home' => 'Sigorta Ana Sayfa',
    'One Page' => 'Tek Sayfa',
    'Multi Page' => 'Cok Sayfa',
    'Home' => 'Ana Sayfa',
    'Pages' => 'Sayfalar',
    'About Company' => 'Hakkimizda',
    'Service' => 'Hizmetler',
    'Service Details' => 'Hizmet Detayi',
    'Service Details 2' => 'Hizmet Detayi 2',
    'Project' => 'Projeler',
    'Team' => 'Ekip',
    'Gallery' => 'Galeri',
    'Team Details' => 'Ekip Detayi',
    'Pricing' => 'Fiyatlandirma',
    'Appoinment' => 'Randevu',
    'Our History' => 'Tarihcemiz',
    'Blog List' => 'Blog Listesi',
    'Blog Grid' => 'Blog Izgarasi',
    'Blog Details' => 'Blog Detayi',
    'Blog Details 02' => 'Blog Detayi 02',
    'Career' => 'Kariyer',
    'Our Mission' => 'Misyonumuz',
    'Partners' => 'Is Ortaklari',
    'Contact 2' => 'Iletisim 2',
    'Shop' => 'Magaza',
    'Shop Details' => 'Urun Detayi',
    'Cart' => 'Sepet',
    'Checkout' => 'Odeme',
    'Account' => 'Hesap',
    'Terms of Condition' => 'Kullanim Kosullari',
    'Privacy Policy' => 'Gizlilik Politikasi',
    'Coming Soon' => 'Cok Yakinda',
    'Read More' => 'Detaylari Gor',
    'Get Started' => 'Hemen Basla',
    'Learn More' => 'Daha Fazla',
    'Get In Touch' => 'Bize Ulasin',
    'Call Us' => 'Bizi Arayin',
    'Email Us' => 'E-posta Gonderin',
    'Our Services' => 'Hizmetlerimiz',
    'Our Projects' => 'Projelerimiz',
    'Our Team' => 'Ekibimiz',
    'Our Blog' => 'Blogumuz',
    'Latest Projects' => 'Son Projeler',
    'Latest News' => 'Son Haberler',
    'View Details' => 'Detaylari Gor',
    'Send Message' => 'Mesaj Gonder',
    'Submit' => 'Gonder',
    'Search' => 'Ara',
    'Previous' => 'Onceki',
    'Next' => 'Sonraki',
    'Frequently Asked Questions' => 'Sikca Sorulan Sorular',
    'Address' => 'Adres',
    'Phone' => 'Telefon',
    'Email' => 'E-posta',
    'Working: 8.00am - 5.00pm' => 'Calisma Saatleri: 09:00 - 18:00',
    'support@invena.com' => 'info@example.com',
    'webmaster@example.com' => 'info@example.com',
    'Create a professional online presence today!' => 'Markaniz icin profesyonel bir dijital vitrin olusturun.',
    'Invena' => 'Kurumsal Site',
];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS)
);

$count = 0;
foreach ($it as $file) {
    if (!$file->isFile()) {
        continue;
    }
    if (strtolower($file->getExtension()) !== 'html') {
        continue;
    }
    $path = $file->getPathname();
    if (str_contains(str_replace('\\', '/', $path), '/documentation/')) {
        continue;
    }

    $html = file_get_contents($path);
    $html = strtr($html, $map);

    if (preg_match('/<meta\s+name="description"\s+content="[^"]*"/i', $html)) {
        $html = preg_replace(
            '/<meta\s+name="description"\s+content="[^"]*"/i',
            '<meta name="description" content="Kurumsal ve profesyonel web sitesi cozumleri"',
            $html,
            1
        ) ?? $html;
    }

    if (preg_match('/<title>.*?<\/title>/is', $html)) {
        $html = preg_replace('/<title>.*?<\/title>/is', '<title>Kurumsal Site</title>', $html, 1) ?? $html;
    }

    file_put_contents($path, $html);
    $count++;
}

file_put_contents($target . '/.btplaces-tr-version', "manual-v1\n");

echo "master-site-tr generated. HTML files processed: {$count}\n";
