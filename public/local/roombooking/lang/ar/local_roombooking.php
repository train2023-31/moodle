<?php
// General plugin strings
$string['pluginname'] = 'خدمة حجز القاعات الدراسية';
$string['registeringroom'] = 'تقديم طلب حجز قاعة دراسية';
$string['selectacourse'] = 'اختر برنامجاً تدريبياً ...';
$string['selectaroom'] = 'اختر قاعة ...';
$string['course'] = 'البرنامج التدريبي';
$string['room'] = 'القاعة';
$string['startdatetime'] = 'تاريخ ووقت البدء';
$string['enddatetime'] = 'تاريخ ووقت الانتهاء';
$string['recurrence'] = 'التكرار';
$string['once'] = 'مرة واحدة';
$string['daily'] = 'يوميًا';
$string['weekly'] = 'أسبوعيًا';
$string['monthly'] = 'شهريًا';
$string['bookroom'] = 'حجز القاعة';
$string['required'] = 'مطلوب';
$string['roomunavailable'] = 'القاعة المحددة غير متاحة في الوقت المختار.';
$string['course_help'] = 'اختر البرنامج التدريبي المرتبط بحجز القاعة. تأكد من اختيار البرنامج التدريبي الصحيح الذي تحتاج القاعة له.';
$string['room_help'] = 'اختر القاعة بناءً على احتياجاتك. يمكنك الاطلاع على نوع القاعة (ثابتة أو ديناميكية) والسعة القصوى بجانب اسم القاعة.';

// Success/Error/Notification Messages
$string['successmessage'] = ' تم الحجز بنجاح! لقد تم حجز قاعتك الدراسية.';
$string['bookingupdated'] = 'تم تحديث الحجز بنجاح.';
$string['errordeletingbooking'] = 'حدث خطأ أثناء حذف الحجز.';
$string['roomnotavailable'] = 'القاعة غير متاحة';
$string['alternativerooms'] = 'القاعات البديلة المتاحة:';
$string['noalternativerooms'] = 'لا توجد قاعات بديلة متاحة للفترة الزمنية المحددة.';
$string['endbeforestart'] = 'يجب أن يكون وقت إنتهاء الحجز بعد وقت البدء';
$string['recurrenceendbeforestart'] = 'لا يمكن أن يكون تاريخ نهاية التكرار قبل تاريخ بداية الحجز';
$string['invalidbookingid'] = 'رقم الحجز غير صالح';
$string['bookingnotfound'] = 'لم يتم العثور على الحجز';
$string['errorupdatingbooking'] = 'حدث خطأ أثناء تحديث الحجز';
$string['erroraddingbooking'] = 'حدث خطأ أثناء إضافة الحجز';
$string['requestsubmitted'] = 'تم إرسال طلب الحجز بنجاح.';
$string['errorcheckingavailability'] = 'حدث خطأ أثناء البحث عن القاعات المتاحة';
$string['availableroomsmessage'] = 'القاعات المتاحة: {$a->available_rooms}';
$string['unexpectederror'] = '!حدث خطأ غير متوقع';
$string['errorprocessingbooking'] = 'حدث خطأ أثناء عملية حجز القاعة';
$string['successaddclassroom'] = 'تم حجز القاعة بنجاح';


// Confirmation Strings
$string['deletebookingconfirm'] = 'هل أنت متأكد أنك تريد حذف هذا الحجز؟ لا يمكن التراجع عن هذا الإجراء.';
$string['deleteconfirmation'] = 'هل أنت متأكد أنك تريد حذف الحجز مع المعرّف {$a}؟';
$string['deleteconfirmationdetails'] = 'هل أنت متأكد أنك تريد حذف الحجز للمقرر: <strong>{$a->course}</strong>، في القاعة: <strong>{$a->room}</strong>، بدءًا من: <strong>{$a->starttime}</strong> وانتهاءً بـ: <strong>{$a->endtime}</strong> مع التكرار: <strong>{$a->recurrence}</strong>?';

// Booking Management & Table UI
$string['username'] = 'اسم المستخدم';
$string['managebookings'] = 'إدارة الحجوزات';
$string['managebookingsdashboard'] = 'لوحة إدارة القاعات الدراسية';
$string['courseid'] = 'معرّف المقرر';
$string['roomid'] = 'معرّف القاعة';
$string['startdate'] = 'تاريخ البدء';
$string['starttime'] = 'وقت البدء';
$string['endtime'] = 'وقت الانتهاء';
$string['edit'] = 'تعديل';
$string['delete'] = 'حذف';
$string['actions'] = 'الإجراءات';
$string['id'] = 'معرّف';
$string['editbooking'] = 'تعديل الحجز';
$string['deletebooking'] = 'حذف الحجز';

// Permissions & Access Control
$string['roombooking:viewbookings'] = 'عرض حجوزات القاعات';
$string['roombooking:managebookings'] = 'إدارة حجوزات القاعات';
$string['roombooking:deletebookings'] = 'حذف حجوزات القاعات';
$string['nopermissions'] = 'ليس لديك الصلاحيات اللازمة للوصول إلى هذه الميزة.';
$string['nocreatepermissions'] = 'ليس لديك الإذن لإنشاء طلب حجز.';
$string['cannoteditbooking'] = 'ليس لديك إذن بتعديل هذا الحجز.';
$string['noactions'] = 'ليس لديك إذن';
$string['nopermission'] = 'ليس لديك الإذن لأداء هذا الإجراء.';
$string['exceedmaximumduration'] = 'تجاوزت مدة الحجز، الحد الأقصى المسموح به وهو {$a} ساعات.';

// Filter & Search Strings
$string['filter'] = 'تصفية';
$string['applyfilters'] = 'تطبيق الفلاتر';
$string['startdate'] = 'تاريخ البدء';
$string['enddate'] = 'تاريخ الانتهاء';
$string['recurrenceenddate'] = 'تاريخ نهاية التكرار';
//$string['any'] = 'أي';
$string['any'] = 'الكل';
$string['course'] = ' البرنامج التدريبي';
$string['bookingday'] = 'يوم الحجز';
$string['classroomfilter'] = 'تصفية حجوزات القاعات الدراسية';
$string['submit'] = 'إرسال';
$string['classroomdata'] = 'بيانات حجز القاعة الدراسية';
$string['noclassroomsfound'] = 'لم يتم العثور على أي حجوزات للقاعات الدراسية حسب الفلاتر المختارة.';
$string['filteredresults'] = 'النتائج المصفاة';
$string['allclassrooms'] = 'جميع حجوزات القاعات الدراسية';

// Group Bookings
$string['confirmdeletegroup'] = 'هل أنت متأكد أنك تريد حذف جميع الحجوزات لمجموعة المعرّف {$a}؟';
$string['groupbookingdeleted'] = 'تم حذف جميع الحجوزات لمجموعة المعرّف {$a}.';
$string['editgroupbooking'] = 'تعديل حجز المجموعة';
$string['groupbookingdetails'] = 'الحجوزات في نفس المجموعة:';
$string['deleteallbookings'] = 'هل تريد حذف جميع الحجوزات في هذه المجموعة؟';
$string['bookingdeleted'] = 'تم حذف جميع الحجوزات في المجموعة بنجاح.';

// Additional Settings
$string['settings'] = 'الإعدادات';
$string['enable'] = 'تفعيل نظام الحجز';
$string['enable_desc'] = 'تفعيل أو تعطيل نظام حجز القاعات الدراسية';
$string['role'] = 'الدور المطلوب';
$string['role_desc'] = 'الدور المطلوب لإجراء الحجوزات';
$string['configuration'] = 'إعدادات الحجز';

// Date & Time Formats
$string['strftimedatetime'] = '%d %B %Y، %H:%M'; // مثال للتنسيق، يمكن تعديله حسب الحاجة

// Additional strings for delete confirmations
$string['deleteallbookings'] = 'هل تريد حذف جميع الحجوزات في هذه المجموعة؟';
$string['bookingdetails'] = 'تفاصيل الحجز للمعرّف: {$a}';

$string['managerooms'] = 'إدارة القاعات';
$string['roomname'] = 'اسم القاعة';
$string['capacity'] = 'السعة';
$string['requiredcapacity'] = 'السعة المطلوبة';
$string['location'] = 'الموقع';
$string['description'] = 'الوصف';
$string['roomadded'] = 'تمت إضافة القاعة بنجاح';
$string['roomupdated'] = 'تم تحديث القاعة بنجاح';
$string['roomdeleted'] = 'تم حذف القاعة بنجاح';
$string['editroom'] = 'تعديل القاعة';
$string['addroom'] = 'إضافة قاعات جديدة';
$string['existingrooms'] = 'القاعات الحالية';
$string['actions'] = 'الإجراءات';
$string['edit'] = 'تعديل';
$string['delete'] = 'حذف';
$string['norooms'] = 'لم يتم العثور على قاعات';
$string['cannotdeleteroom'] = 'لا يمكن حذف القاعة لأنها مرتبطة بحجوزات حالية';
$string['required'] = 'هذا الحقل مطلوب';
$string['savechanges'] = 'حفظ التغييرات';
$string['cancel'] = 'إلغاء';

$string['invalidcapacity'] = 'يرجى إدخال سعة صالحة.';
$string['roomexceedsmaxcapacity'] = 'السعة المطلوبة تتجاوز الحد الأقصى المسموح به للقاعات';
$string['validnumber'] = 'يرجى إدخال رقم صحيح.';
$string['invalidrecurrenceenddate'] = '!تاريخ إنتهاء التكرار غير صحيح';
$string['invaliddatetime'] = 'تاريخ البدء/تاريخ الإنتهاء غير صالح';
$string['invalidtimetobook'] = '!تاريخ بداية الحجز يجب أن يكون قبل تاريخ الإنتهاء';
$string['recurrenceendbeforeend'] = ' !تاريخ إنتهاء التكرار يجب أن يكون قبل تاريخ إنتهاء الحجز';
$string['invalidrecurrencetype'] = '!نوع التكرار غير صحيح';
$string['recurrenceintervalisnotsetproperly'] = 'لم يتم ضبط الفاصل الزمني للتكرار بشكل صحيح';

$string['roomtype'] = 'نوع القاعة';
$string['fixed'] = 'ثابت';
$string['dynamic'] = 'متغير';
$string['invalidroomtype'] = 'تم اختيار نوع قاعات غير صحيح.';
$string['invalidcapacity'] = 'يرجى إدخال سعة صالحة أكبر من الصفر.';

// Column Headers
$string['column_id'] = '#';
$string['column_course'] = 'البرنامج التدريبي';
$string['column_room'] = 'القاعة';
$string['column_capacity'] = 'السعة';
$string['column_start_date'] = 'تاريخ البدء';
$string['column_start_time'] = 'وقت البدء';
$string['column_end_time'] = 'وقت الانتهاء';
$string['column_recurrence'] = 'التكرار';
$string['column_user'] = 'منفذ الطلب';
$string['actions_label'] = 'الإجراءات';
$string['column_status'] = 'الحالة';

// New Strings for Modals
$string['approve_request'] = 'الموافقة';
$string['reject_request'] = 'رفض';
$string['approve_confirmation'] = 'هل أنت متأكد من الموافقة على هذاالطلب؟';
$string['reject_confirmation'] = 'هل أنت متأكد من رفض هذاالطلب؟';
$string['reject_note_placeholder'] = 'يرجى تقديم ملاحظة لرفض الطلب';
$string['reject_note_required'] = 'مطلوب تقديم ملاحظة للرفض.';
$string['approve_success'] = 'تمت الموافقة على الطلب بنجاح.';
$string['reject_success'] = 'تم رفض الطلب بنجاح.';
$string['approve_error'] = 'حدث خطأ أثناء الموافقة على الطلب.';
$string['reject_error'] = 'حدث خطأ أثناء رفض الطلب.';
$string['confirm'] = 'تأكيد';
$string['cancel'] = 'إلغاء';
$string['exportcsv'] = 'تصدير CSV';
$string['clearfilters'] = 'إعادة التصفية';

// Status labels
$string['status_pending'] = 'قيد الانتظار';
$string['status_approved'] = 'تمت الموافقة';
$string['status_rejected'] = 'تم الرفض';
$string['unknown_status'] = 'قيد الإجراء  ';
$string['awaiting_approval'] = 'بانتظار الموافقة في مرحلة أخرى';


$string['recurrence_none'] = 'لايوجد';
$string['recurrence_daily'] = 'يومي';
$string['recurrence_weekly'] = 'أسبوعي';
$string['recurrence_monthly'] = 'شهري';
$string['recurrence_unknown'] = 'غير معروف';

// Workflow strings
$string['booking_approved'] = 'تمت الموافقة على الحجز بنجاح';
$string['booking_rejected'] = 'تم رفض الحجز بنجاح';
$string['confirm_title'] = 'التأكيد';
$string['confirm_approve'] = 'هل أنت متأكد أنك تريد الموافقة على هذا الحجز؟';
$string['confirm_reject'] = 'هل أنت متأكد أنك تريد رفض هذا الحجز؟';
$string['rejection_reason'] = ' تم رفض طلب الحجز الخاص بك. السبب';
$string['approval_note'] = 'ملاحظة الموافقة';
$string['awaiting_approval'] = 'بانتظار الموافقة في مرحلة مختلفة';
$string['statusalreadyfinal'] = 'تمت الموافقة النهائية على هذا الحجز من قبل';
$string['cannotrejectapproved'] = 'لا يمكن رفض حجز تمت الموافقة عليه من قبل';
$string['cannotreject'] = 'لا يمكن رفض هذا الحجز في مرحلته الحالية';

// Management Dashboard
$string['manage'] = 'إدارة';
$string['viewbookings'] = 'عرض الحجوزات';
$string['viewfilterbookings'] = 'عرض وتصفية حجوزات {$a->type}';
$string['reports'] = 'التقارير';
$string['generatereports'] = 'إنشاء تقارير وإحصائيات الحجز';
$string['vieweditrooms'] = 'عرض وإنشاء وتعديل تفاصيل القاعات الدراسية';
$string['bookinginterface'] = 'واجهة الحجز';
$string['managementdashboard'] = 'لوحة الإدارة';

// Additional capability strings
$string['roombooking:managerooms'] = 'إدارة حجز القاعات الدراسية';

$string['invalidroomid'] = 'معرّف القاعة غير صالح.';
$string['roomnotavailableWithlistofrooms'] = 'القاعة المحددة غير متاحة. القاعات البديلة المتاحة: {$a->available_rooms}.';

// إظهار/إخفاء القاعات المؤرشفة (المخفية)
$string['showhidden'] = 'إظهار القاعات المخفية';
$string['showonlyvisible'] = 'إظهار القاعات الظاهرة فقط';
$string['hide'] = 'إخفاء';
$string['restore'] = 'استرجاع';

