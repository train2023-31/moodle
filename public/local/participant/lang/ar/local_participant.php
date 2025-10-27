<?php
// This file is part of the Participant plugin for Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'إدارة المحاضر /لاعب الدور';
$string['viewrequests'] = 'عرض الطلبات';
$string['addrequest'] = 'إضافة طلب محاضر/لاعب دور';
$string['course'] = 'البرنامج التدريبي';
$string['plan'] = 'الخطة السنوية';
$string['type'] = 'نوع الطلب';
$string['externallecturer'] = 'محاضر خارجي';
$string['orgname'] = 'اسم المؤسسة';
$string['days_hours'] = 'أيام/ساعات';
$string['contract_amount'] = 'التكلفة';
$string['status'] = 'الحالة';
$string['isapproved'] = 'الموافقة';
$string['cost'] = 'التكلفة';
$string['dayshours'] = 'المدة بالأيام';
$string['organization'] = 'المؤسسة';
$string['participant'] = 'اسم المشارك';
$string['isinside'] = 'داخل';
$string['user'] = 'المنتسب';
$string['searchemployee'] = 'ابدأ بالكتابة للبحث عن منتسب...';
$string['searchlecturer'] = 'ابدأ بالكتابة للبحث عن محاضر...';
$string['nocoursesfound'] = 'لا توجد دورات';
$string['noapprovedcoursesfound'] = 'لا توجد دورات معتمدة';
$string['noannualplanwarning'] = 'تحذير: البرنامج التدريبي "{$a}" لا يحتوي على بيانات خطة سنوية. سيتم معالجة الطلب بدون ربط بالخطة السنوية.';
$string['usercreationwarning'] = 'تحذير: لم يتم العثور على/إنشاء مستخدم في مودل للموظف برقم وظيفي "{$a}". سيتم معالجة الطلب ولكن قد يتطلب تخصيص المستخدم يدوياً.';
$string['requestdate'] = 'تاريخ الطلب';
$string['servicedate_help'] = 'تاريخ الخدمة';
$string['servicedate_help_help'] = 'التاريخ الذي تكون فيه خدمة/عمل المشارك مطلوبة فعلياً. هذا هو التاريخ الذي سيوفر فيه لاعب الدور أو المحاضر أو المشارك الآخر خدماته.';
$string['invalidnumber'] = 'الرجاء ادخال رقم صحيح';
$string['filter'] = 'تصفية';
$string['allcourses'] = 'جميع البرامج التدريبية';
$string['allparticipanttypes'] = 'جميع أنواع المشاركين';
$string['allstatuses'] = 'جميع الحالات';
$string['statusfilter'] = 'تصفية الحالة';
$string['clearfilters'] = 'إعادة التصفية ';
$string['requestadded'] = 'تم إرسال الطلب بنجاح ';

// New strings for template
$string['participanttype'] = 'نوع المشارك';
$string['internal'] = 'داخلي';
$string['external'] = 'خارجي';
$string['contractduration'] = 'مدة العقد';
$string['contractcost'] = 'تكلفة العقد';
$string['requeststatus'] = 'حالة الطلب';
$string['days'] = 'يوم';
$string['hours'] = 'ساعة';
$string['dynamic'] = 'ديناميكي';
$string['currency'] = 'ر.ع';
$string['approve'] = 'الموافقة';
$string['reject'] = 'الرفض';
$string['confirmapprove'] = 'هل تريد اعتماد الطلب؟';
$string['confirmreject'] = 'هل تريد رفض الطلب؟';
$string['recordsperpage'] = 'سجل لكل صفحة';
$string['noactions'] = 'لا توجد إجراءات متاحة';
$string['requestapproved'] = 'تم اعتماد الطلب';
$string['requestrejected'] = 'تم رفض الطلب';
$string['finalstatus'] = 'الحالة النهائية';
$string['rejectionreason'] = 'سبب الرفض';
$string['noreason'] = 'لم يتم تقديم سبب';

// Capability strings
$string['participant:addrequest'] = 'إضافة طلب المشارك';
$string['participant:view'] = 'عرض معلومات المشارك';


//Participant Types
$string['roleplayer'] = 'لاعب دور';
$string['assistantlecturer'] = 'محاضر مشارك';
$string['externallecturer'] = 'محاضر خارجي';

// Help text for compensation calculation
$string['compensation_help'] = 'حساب مبلغ التعويض';
$string['compensation_help_help'] = 'يتم حساب مبلغ التعويض تلقائياً حسب نوع المشارك:<br/>
• <strong>أنواع لاعب الدور:</strong> المدة (بالأيام) × السعر الثابت لكل يوم<br/>
• <strong>أنواع المحاضر/المقيم:</strong> المدة (بالساعات) × السعر الثابت لكل ساعة<br/>
• <strong>المحاضر الخارجي:</strong> يتطلب إدخال يدوي (يختلف حسب الخبير)';

// Help text for contract period
$string['contractperiod_help'] = 'إرشادات فترة العقد';
$string['contractperiod_help_help'] = 'أدخل فترة العقد بناءً على نوع المشارك المختار:<br/>
• <strong>الأنواع المحسوبة بالأيام:</strong> أدخل عدد الأيام (مثلاً: 5 لـ 5 أيام)<br/>
• <strong>الأنواع المحسوبة بالساعات:</strong> أدخل عدد الساعات (مثلاً: 8 لـ 8 ساعات)<br/>
• <strong>الأنواع الديناميكية:</strong> أدخل الوحدات المناسبة حسب الحاجة<br/>
<em>طريقة الحساب موضحة بين قوسين بجانب كل نوع مشارك أعلاه.</em>';

// Error messages
$string['invalidcourseid'] = 'دورة غير صحيحة';
$string['coursemissingidnumber'] = 'البرنامج التدريبي المختار يفتقر إلى رمز البرنامج التدريبي (idnumber)';
$string['invalidcourseselection'] = 'البرنامج التدريبي المختار لا يحتوي على بيانات خطة سنوية مقابلة';
$string['noplandata'] = 'لم يتم العثور على بيانات خطة سنوية لرمز البرنامج التدريبي: {$a}';
$string['inserterror'] = 'حدث خطأ أثناء حفظ الطلب. يرجى المحاولة مرة أخرى.';

// User creation messages
$string['moodleusercreated'] = 'تم إنشاء حساب مستخدم في مودل للرقم الوظيفي: {$a}';
$string['userenrolled'] = 'تم تسجيل المستخدم في الدورة بنجاح';
$string['enrollmentfailed'] = 'فشل في تسجيل المستخدم في الدورة';

// Oracle data messages
$string['oracledatastored'] = 'تم تخزين بيانات أوراكل للرقم الوظيفي: {$a}';
$string['pfnumber'] = 'الرقم الوظيفي';

// Audit fields
$string['createdby'] = 'أنشئ بواسطة';
$string['createddate'] = 'تاريخ الإنشاء';
$string['clause'] = 'البند';
$string['fundingtype'] = 'نوع التمويل';