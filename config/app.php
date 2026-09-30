<?php
declare(strict_types=1);

function load_env_file(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if ($name !== '' && getenv($name) === false) {
            putenv($name . '=' . trim($value, " \t\n\r\0\x0B\"'"));
            $_ENV[$name] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
}

load_env_file(dirname(__DIR__) . '/.env');
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Africa/Cairo');

const DEFAULT_THEME = [
    'primary_light' => '#174f7d',
    'secondary_light' => '#153754',
    'accent_light' => '#d52837',
    'background_light' => '#f6f8fb',
    'surface_light' => '#ffffff',
    'text_light' => '#1b2c3a',
    'primary_dark' => '#7db5e1',
    'secondary_dark' => '#c4d8e8',
    'accent_dark' => '#ff727e',
    'background_dark' => '#101a26',
    'surface_dark' => '#192838',
    'text_dark' => '#eff5f9',
];

const TRANSLATIONS = [
    'ar' => [
        'site_tagline' => 'رعاية صحية أقرب إليك', 'home' => 'الرئيسية', 'campaigns' => 'الحملات',
        'branches' => 'الفروع', 'book' => 'احجز موعدك', 'theme' => 'تغيير المظهر', 'language' => 'English',
        'hero_kicker' => 'صحتك تبدأ بخطوة', 'hero_title' => 'رعاية مخبرية موثوقة، أقرب إليك',
        'hero_text' => 'اكتشف حملاتنا الصحية واختر الفرع والموعد المناسبين لك.', 'explore_campaigns' => 'اكتشف الحملات',
        'our_campaigns' => 'حملاتنا الصحية', 'campaigns_intro' => 'برامج وفحوصات صحية صُممت لتناسب احتياجاتك.',
        'view_all' => 'عرض كل الحملات', 'our_branches' => 'فروعنا', 'branches_intro' => 'اختر الفرع الأقرب إليك.',
        'book_now' => 'احجز الآن', 'learn_more' => 'التفاصيل', 'no_campaigns' => 'لا توجد حملات متاحة حالياً.',
        'no_branches' => 'لا توجد فروع متاحة حالياً.', 'about' => 'من نحن', 'contact' => 'تواصل معنا',
        'working_hours' => 'ساعات العمل', 'address' => 'العنوان', 'phone' => 'الهاتف', 'map' => 'عرض الخريطة',
        'booking_title' => 'احجز موعدك', 'booking_intro' => 'أدخل بياناتك وسنتولى تأكيد تفاصيل الحجز.',
        'full_name' => 'الاسم بالكامل', 'phone_number' => 'رقم الهاتف', 'email' => 'البريد الإلكتروني',
        'campaign' => 'الحملة', 'choose_campaign' => 'اختر الحملة', 'branch' => 'الفرع', 'choose_branch' => 'اختر الفرع',
        'date' => 'التاريخ المناسب', 'time' => 'الوقت المناسب', 'notes' => 'ملاحظات إضافية',
        'consent' => 'أوافق على استخدام بياناتي للتواصل بشأن هذا الحجز.', 'submit_booking' => 'إرسال طلب الحجز',
        'booking_success' => 'تم استلام طلبك بنجاح. سنتواصل معك لتأكيد الموعد.',
        'booking_error' => 'تعذر إتمام الحجز. تحقق من البيانات وحاول مرة أخرى.',
        'required' => 'يرجى تعبئة هذا الحقل.', 'invalid_phone' => 'أدخل رقم هاتف صحيحاً.',
        'invalid_date' => 'اختر تاريخاً من اليوم أو بعده.', 'select_campaign' => 'اختر حملة متاحة.',
        'select_branch' => 'اختر فرعاً متاحاً لهذه الحملة.', 'slot_taken' => 'هذا الموعد محجوز. اختر وقتاً آخر.',
        'campaign_full' => 'اكتمل العدد المتاح لهذه الحملة.', 'close' => 'إغلاق', 'no_image' => 'صورة الحملة',
        'footer_note' => 'نهتم بصحتك في كل خطوة.', 'not_found' => 'الصفحة المطلوبة غير موجودة.',
        'select_time' => 'اختر الوقت', 'campaign_details' => 'عن الحملة', 'capacity_open' => 'الحجز متاح',
        'privacy_note' => 'بياناتك محفوظة وتستخدم فقط لمتابعة طلب الحجز.', 'menu' => 'القائمة',
        'outside_hours' => 'الوقت المختار خارج ساعات عمل الفرع.', 'already_booked' => 'لديك حجز نشط لهذه الحملة بالفعل.',
    ],
    'en' => [
        'site_tagline' => 'Healthcare, closer to you', 'home' => 'Home', 'campaigns' => 'Campaigns',
        'branches' => 'Branches', 'book' => 'Book an appointment', 'theme' => 'Toggle appearance', 'language' => 'العربية',
        'hero_kicker' => 'Your health starts with one step', 'hero_title' => 'Trusted lab care, closer to you',
        'hero_text' => 'Explore our health campaigns and choose a branch and time that work for you.', 'explore_campaigns' => 'Explore campaigns',
        'our_campaigns' => 'Health campaigns', 'campaigns_intro' => 'Screening and care programs designed around your needs.',
        'view_all' => 'View all campaigns', 'our_branches' => 'Our branches', 'branches_intro' => 'Choose the branch closest to you.',
        'book_now' => 'Book now', 'learn_more' => 'Learn more', 'no_campaigns' => 'There are no campaigns available right now.',
        'no_branches' => 'There are no branches available right now.', 'about' => 'About us', 'contact' => 'Contact',
        'working_hours' => 'Working hours', 'address' => 'Address', 'phone' => 'Phone', 'map' => 'View map',
        'booking_title' => 'Book an appointment', 'booking_intro' => 'Share your details and we’ll follow up to confirm your appointment.',
        'full_name' => 'Full name', 'phone_number' => 'Phone number', 'email' => 'Email', 'campaign' => 'Campaign',
        'choose_campaign' => 'Choose a campaign', 'branch' => 'Branch', 'choose_branch' => 'Choose a branch',
        'date' => 'Preferred date', 'time' => 'Preferred time', 'notes' => 'Additional notes',
        'consent' => 'I agree that my details may be used to follow up about this appointment.',
        'submit_booking' => 'Send booking request', 'booking_success' => 'Your request was received. We’ll contact you to confirm the appointment.',
        'booking_error' => 'We could not complete the booking. Check your details and try again.',
        'required' => 'Please complete this field.', 'invalid_phone' => 'Enter a valid phone number.',
        'invalid_date' => 'Choose today or a future date.', 'select_campaign' => 'Choose an available campaign.',
        'select_branch' => 'Choose a branch available for this campaign.', 'slot_taken' => 'That time is already booked. Choose another time.',
        'campaign_full' => 'This campaign has reached capacity.', 'close' => 'Close', 'no_image' => 'Campaign image',
        'footer_note' => 'Here for your health, every step of the way.', 'not_found' => 'The requested page could not be found.',
        'select_time' => 'Choose a time', 'campaign_details' => 'About this campaign', 'capacity_open' => 'Booking is open',
        'privacy_note' => 'Your details are used only to follow up on this booking.', 'menu' => 'Menu',
        'outside_hours' => 'The selected time is outside this branch’s working hours.', 'already_booked' => 'You already have an active booking for this campaign.',
    ],
];
