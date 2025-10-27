<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Arabic language strings for local_status
 *
 * @package   local_status
 */

// Plugin strings
$string['pluginname'] = 'نظام إدارة سير العمل';
$string['workflowdashboard'] = 'لوحة تحكم سير العمل';

// Tab strings
$string['workflows'] = 'سير العمل';
$string['workflowsteps'] = 'خطوات سير العمل';
$string['workflowtransitions'] = 'الانتقالات';
$string['workflowhistory'] = 'التاريخ';
$string['workflowtemplates'] = 'القوالب';

// Workflow management
$string['manageworkflows'] = 'إدارة سير العمل';
$string['addworkflow'] = 'إضافة سير عمل';
$string['editworkflow'] = 'تعديل سير العمل';
$string['copyworkflow'] = 'نسخ سير العمل';
$string['deleteworkflow'] = 'حذف سير العمل';
$string['noworkflows'] = 'لم يتم العثور على سير عمل. قم بإنشاء أول سير عمل للبدء.';
$string['workflow_in_use'] = 'لا يمكن حذف سير العمل لأنه قيد الاستخدام حالياً.';

// Workflow steps
$string['managesteps'] = 'إدارة الخطوات';
$string['addstep'] = 'إضافة خطوة';
$string['editstep'] = 'تعديل خطوة';
$string['deletestep'] = 'حذف خطوة';
$string['nosteps'] = 'لم يتم تعريف خطوات لسير العمل هذا.';
$string['stepsfor'] = 'خطوات لـ: {$a}';
$string['dragtoReorder'] = 'اسحب الصفوف لإعادة ترتيب الخطوات';

// Common fields
$string['name'] = 'الاسم';
$string['displayname'] = 'اسم العرض';
$string['displayname_en'] = 'اسم العرض (الإنجليزية)';
$string['displayname_ar'] = 'اسم العرض (العربية)';
$string['plugin'] = 'الإضافة';
$string['steps'] = 'الخطوات';
$string['status'] = 'الحالة';
$string['actions'] = 'الإجراءات';
$string['capability'] = 'الصلاحية';
$string['color'] = 'اللون';
$string['flags'] = 'العلامات';
$string['active'] = 'نشط';
$string['inactive'] = 'غير نشط';
$string['sortorder'] = 'ترتيب الفرز';
$string['sequence'] = 'التسلسل';
$string['workflow'] = 'سير العمل';
$string['icon'] = 'الأيقونة';
$string['allstepsactive'] = 'جميع الخطوات نشطة';

// Step flags
$string['initial'] = 'ابتدائي';
$string['final'] = 'نهائي';

// Approval types
$string['approvaltype'] = 'نوع الموافقة';
$string['capabilitybased'] = 'قائم على الصلاحيات';
$string['userbased'] = 'قائم على المستخدمين';
$string['approval_any'] = 'أي مستخدم';
$string['approval_capability'] = 'قائم على الصلاحيات (قديم)';
$string['approval_user'] = 'مستخدمون محددون';

// Dynamic Approver Management
$string['manageapprovers'] = 'إدارة الموافقين';
$string['stepinformation'] = 'معلومات الخطوة';
$string['addapprover'] = 'إضافة موافق';
$string['selectuser'] = 'اختر مستخدم';
$string['required'] = 'مطلوب';
$string['optional'] = 'اختياري';
$string['currentapprovers'] = 'الموافقون الحاليون';
$string['noapprovers'] = 'لم يتم تكوين موافقين لهذه الخطوة.';
$string['approverorderinstructions'] = 'سيتم إخطار الموافقين بالترتيب المعروض. استخدم أزرار الأسهم لإعادة ترتيبهم.';
$string['onlyuserbased_approvers'] = 'إدارة الموافقين متاحة فقط لخطوات الموافقة القائمة على المستخدمين.';
$string['moveup'] = 'نقل لأعلى';
$string['movedown'] = 'نقل لأسفل';
$string['confirmremoveapprover'] = 'هل أنت متأكد من أنك تريد إزالة هذا الموافق؟';
$string['back'] = 'رجوع';

// Approver management messages
$string['approveradded'] = 'تم إضافة الموافق بنجاح';
$string['approverremoved'] = 'تم إزالة الموافق بنجاح';
$string['approversreordered'] = 'تم إعادة ترتيب الموافقين بنجاح';
$string['error_adding_approver'] = 'خطأ في إضافة الموافق';
$string['error_removing_approver'] = 'خطأ في إزالة الموافق';
$string['error_reordering_approvers'] = 'خطأ في إعادة ترتيب الموافقين';
$string['approver_already_exists'] = 'المستخدم موافق بالفعل لهذه الخطوة';

// Workflow instance management
$string['workflowstatus'] = 'حالة سير العمل';
$string['currentstep'] = 'الخطوة الحالية';
$string['nextapprover'] = 'الموافق التالي';
$string['cannotapprove'] = 'ليس لديك صلاحية للموافقة على هذا الطلب';
$string['cannotreject'] = 'ليس لديك صلاحية لرفض هذا الطلب';
$string['workflowstarted'] = 'تم بدء سير العمل';
$string['workflowapproved'] = 'تم الموافقة على الطلب';
$string['workflowrejected'] = 'تم رفض الطلب';
$string['movedtonextstep'] = 'تم الانتقال إلى الخطوة التالية';

// Workflow errors
$string['workflow_instance_exists'] = 'يوجد مثيل سير عمل بالفعل لهذا السجل';
$string['no_initial_step'] = 'لم يتم العثور على خطوة ابتدائية لسير العمل هذا';
$string['cannot_approve'] = 'لا يمكنك الموافقة على هذا الطلب';
$string['cannot_reject'] = 'لا يمكنك رفض هذا الطلب';

// API for other plugins
$string['sequential_workflow_created'] = 'تم إنشاء سير العمل المتسلسل بنجاح';
$string['workflow_started'] = 'تم بدء سير العمل بنجاح';
$string['workflow_approval_processed'] = 'تم معالجة موافقة سير العمل';

// Enhanced workflow display
$string['approvalsequence'] = 'تسلسل الموافقات';
$string['approver'] = 'الموافق';
$string['status_pending'] = 'قيد الانتظار';
$string['status_inprogress'] = 'قيد التنفيذ';
$string['status_approved'] = 'تم الإعتماد';
$string['status_rejected'] = 'مرفوض';
$string['waitingfor'] = 'في انتظار: {$a}';
$string['approvedby'] = 'تم الاعتماد من قبل: {$a}';
$string['rejectedby'] = 'تم الرفض من قبل: {$a}';

// Quick workflow setup
$string['quicksetup'] = 'إعداد سريع';
$string['createsequentialworkflow'] = 'إنشاء سير عمل متسلسل';
$string['workflowname_placeholder'] = 'مثل: my_plugin_approval';
$string['workflowdisplay_placeholder'] = 'مثل: عملية موافقة الإضافة الخاصة بي';
$string['selectapprovers'] = 'اختر الموافقين (بالترتيب)';
$string['addapprover_button'] = 'إضافة موافق';
$string['removeapprover_button'] = 'إزالة';
$string['createworkflow_button'] = 'إنشاء سير العمل';
$string['workflowcreated_success'] = 'تم إنشاء سير العمل "{$a}" بنجاح مع {$b} موافق';

// Step positioning and reordering
$string['insertposition'] = 'موقع الإدراج';
$string['insertpositionhelp'] = 'اختر مكان إدراج هذه الخطوة الجديدة في تسلسل سير العمل.';
$string['selectposition'] = 'اختر الموقع...';
$string['firststep'] = 'كخطوة أولى';
$string['beforestep'] = 'قبل: {$a}';
$string['afterstep'] = 'بعد: {$a}';

// Form help buttons
$string['stepnamehelp'] = 'معرف فريد لهذه الخطوة (لا يمكن تغييره بعد الإنشاء).';
$string['namehelp'] = 'معرف فريد لسير العمل هذا (لا يمكن تغييره بعد الإنشاء).';
$string['displayname_ar_help'] = 'اسم العرض بالعربية (مطلوب).';
$string['pluginhelp'] = 'اسم الإضافة المرتبطة (اختياري).';
$string['capabilityhelp'] = 'صلاحية مودل المطلوبة للموافقة (مثل: moodle/site:config).';
$string['approvaltypehelp'] = 'كيفية التعامل مع الموافقات في هذه الخطوة.';

// Workflow forms
$string['workflowname'] = 'اسم سير العمل';
$string['workflowname_help'] = 'الاسم الداخلي لسير العمل (يستخدم في الكود)';
$string['workflowdisplayname'] = 'اسم العرض';
$string['workflowdisplayname_help'] = 'الاسم المعروض للمستخدمين';
$string['workflowplugin'] = 'الإضافة';
$string['workflowplugin_help'] = 'الإضافة التي تملك هذا سير العمل';
$string['workflowactive'] = 'نشط';
$string['workflowactive_help'] = 'ما إذا كان سير العمل نشط حالياً';

// Step forms
$string['stepname'] = 'اسم الخطوة';
$string['stepname_help'] = 'الاسم الداخلي للخطوة';
$string['stepdisplayname'] = 'اسم العرض';
$string['stepdisplayname_help'] = 'الاسم المعروض للمستخدمين';
$string['stepcapability'] = 'الصلاحية المطلوبة';
$string['stepcapability_help'] = 'صلاحية مودل المطلوبة للموافقة على هذه الخطوة';
$string['stepcolor'] = 'اللون';
$string['stepcolor_help'] = 'اللون المستخدم في عناصر واجهة المستخدم لهذه الخطوة';
$string['stepicon'] = 'الأيقونة';
$string['stepicon_help'] = 'فئة الأيقونة المستخدمة لهذه الخطوة';
$string['stepinitial'] = 'الخطوة الابتدائية';
$string['stepinitial_help'] = 'هل هذه الخطوة الأولى في سير العمل؟';
$string['stepfinal'] = 'الخطوة النهائية';
$string['stepfinal_help'] = 'هل هذه الخطوة الأخيرة في سير العمل؟';

// Enhanced step form fields
$string['mustspecifyworkflow'] = 'يجب تحديد سير عمل لإضافة خطوة إليه';
$string['stepnameexists'] = 'توجد خطوة بهذا الاسم في سير العمل هذا بالفعل';
$string['invalidcolor'] = 'تنسيق لون غير صحيح. يرجى استخدام التنسيق السادس عشري (مثل: #ff0000)';
$string['stepinuse'] = 'لا يمكن حذف الخطوة لأنها قيد الاستخدام حالياً';
$string['createstep'] = 'إنشاء خطوة';
$string['updatestep'] = 'تحديث خطوة';
$string['stepflags'] = 'علامات الخطوة';
$string['isinitial'] = 'الخطوة الابتدائية';
$string['isinitial_desc'] = 'جعل هذه خطوة البداية في سير العمل';
$string['isinitial_help'] = 'الخطوة الابتدائية هي حيث تبدأ جميع مثيلات سير العمل. يمكن أن يكون هناك خطوة ابتدائية واحدة فقط لكل سير عمل.';
$string['isfinal'] = 'الخطوة النهائية';
$string['isfinal_desc'] = 'جعل هذه خطوة النهاية في سير العمل';
$string['isfinal_help'] = 'الخطوة النهائية هي حيث تنتهي مثيلات سير العمل. يمكن أن يكون هناك خطوة نهائية واحدة فقط لكل سير عمل.';
$string['initialstepexists'] = 'توجد خطوة ابتدائية بالفعل لسير العمل هذا';
$string['finalstepexists'] = 'توجد خطوة نهائية بالفعل لسير العمل هذا';
$string['stepactive_desc'] = 'ما إذا كانت هذه الخطوة نشطة حالياً ويمكن استخدامها';

// Delete step confirmation
$string['confirmdeletestep'] = 'هل أنت متأكد من أنك تريد حذف الخطوة "{$a->stepname}" من سير العمل "{$a->workflowname}"؟';
$string['deletestepwillapprovers'] = 'هذا سيؤدي أيضاً إلى إزالة {$a} موافق مخصص لهذه الخطوة.';

// Workflow selection
$string['selectworkflow'] = 'اختر سير عمل';

// Messages
$string['workflowcreated'] = 'تم إنشاء سير العمل بنجاح.';
$string['workflowupdated'] = 'تم تحديث سير العمل بنجاح.';
$string['workflowdeleted'] = 'تم حذف سير العمل بنجاح';
$string['workflowcopied'] = 'تم نسخ سير العمل بنجاح';
$string['stepcreated'] = 'تم إنشاء الخطوة بنجاح.';
$string['stepupdated'] = 'تم تحديث الخطوة بنجاح.';
$string['stepdeleted'] = 'تم حذف الخطوة بنجاح';
$string['stepsreordered'] = 'تم إعادة ترتيب الخطوات بنجاح';

// Error messages
$string['invalidworkflow'] = 'سير العمل المحدد غير صحيح';
$string['invalidstep'] = 'الخطوة المحددة غير صحيحة';
$string['cannotdeletestep'] = 'لا يمكن حذف الخطوة لأنها قيد الاستخدام حالياً';
$string['duplicateworkflowname'] = 'يوجد سير عمل بهذا الاسم بالفعل.';
$string['duplicatestepname'] = 'توجد خطوة بهذا الاسم في سير العمل هذا بالفعل.';
$string['capabilityrequired'] = 'الصلاحية مطلوبة للموافقة القائمة على الصلاحيات.';
$string['error_reordering_steps'] = 'حدث خطأ أثناء إعادة ترتيب الخطوات.';
$string['missingworkflow'] = 'معرف سير العمل مطلوب.';
$string['workflow_has_steps'] = 'لا يمكن حذف سير العمل لأنه يحتوي على خطوات.';

// Enhanced error handling
$string['error_occurred'] = 'حدث خطأ: {$a}';
$string['cannot_delete_active_workflow'] = 'لا يمكن حذف سير عمل نشط. يرجى إلغاء تفعيله أولاً.';
$string['step_has_dependencies'] = 'لا يمكن حذف الخطوة لأن لديها تبعيات أو قيد الاستخدام.';

// Status messages
$string['workflowstatuschanged'] = 'تم تغيير حالة سير العمل إلى {$a}';
$string['stepstatuschanged'] = 'تم تغيير حالة الخطوة إلى {$a}';
$string['selectworkflowfirst'] = 'يرجى اختيار سير عمل من القائمة المنسدلة أعلاه لإدارة خطواته.';
$string['nostepshelp'] = 'سير العمل هذا لا يحتوي على خطوات بعد. استخدم زر "إضافة خطوة" أعلاه لإنشاء الخطوة الأولى وبدء بناء سير العمل.';

// Color names for dropdown
$string['color_gray'] = 'رمادي (افتراضي)';
$string['color_yellow'] = 'أصفر (قيد الانتظار)';
$string['color_blue'] = 'أزرق (مراجعة)';
$string['color_green'] = 'أخضر (مصدق)';
$string['color_red'] = 'أحمر (مرفوض)';
$string['color_orange'] = 'برتقالي';
$string['color_purple'] = 'بنفسجي';
$string['color_teal'] = 'أزرق مخضر';

// Action buttons
$string['go'] = 'انطلق';
$string['save'] = 'حفظ';
$string['cancel'] = 'إلغاء';
$string['edit'] = 'تعديل';
$string['delete'] = 'حذف';
$string['copy'] = 'نسخ';
$string['activate'] = 'تفعيل';
$string['deactivate'] = 'إلغاء تفعيل';
$string['activated'] = 'تم التفعيل';
$string['deactivated'] = 'تم إلغاء التفعيل';
$string['confirmdelete'] = 'هل أنت متأكد من أنك تريد حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.';
$string['view'] = 'عرض';
$string['approve'] = 'موافقة';
$string['reject'] = 'رفض';
$string['submit'] = 'إرسال';

// Transition management
$string['transitionscomingsoon'] = 'إدارة الانتقالات قريباً';
$string['historycomingsoon'] = 'عرض التاريخ قريباً';
$string['templatescomingsoon'] = 'قوالب سير العمل قريباً';

// Capabilities
$string['status:manage'] = 'إدارة نظام سير العمل';
$string['status:viewhistory'] = 'عرض تاريخ سير العمل';

// New Department-Specific Capabilities
$string['status:manage_workflows'] = 'إدارة نظام سير العمل';
$string['status:view_all_requests'] = 'عرض جميع طلبات سير العمل';

// أسماء عرض خطوات سير العمل
// خطوات سير عمل الخطة السنوية
$string['status:annual_plan_workflow_step1'] = 'الخطة السنوية - الخطوة 1: مراجعة ض.د';
$string['status:annual_plan_workflow_step2'] = 'الخطة السنوية - الخطوة 2: مراجعة ض.ق';
$string['status:annual_plan_workflow_step3'] = 'الخطة السنوية - الخطوة 3: مراجعة ض.ق.خ';
$string['status:annual_plan_workflow_step4'] = 'الخطة السنوية - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل حجز القاعات
$string['status:classroom_workflow_step1'] = 'حجز القاعة - الخطوة 1: مراجعة ض.د';
$string['status:classroom_workflow_step2'] = 'حجز القاعة - الخطوة 2: مراجعة ض.ق';
$string['status:classroom_workflow_step3'] = 'حجز القاعة - الخطوة 3: مراجعة ض.ق.خ';
$string['status:classroom_workflow_step4'] = 'حجز القاعة - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل الخدمات الحاسوبية
$string['status:computer_workflow_step1'] = 'الخدمة الحاسوبية - الخطوة 1: مراجعة ض.د';
$string['status:computer_workflow_step2'] = 'الخدمة الحاسوبية - الخطوة 2: مراجعة ض.ق';
$string['status:computer_workflow_step3'] = 'الخدمة الحاسوبية - الخطوة 3: مراجعة ض.ق.خ';
$string['status:computer_workflow_step4'] = 'الخدمة الحاسوبية - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل طلبات الدورات
$string['status:course_workflow_step1'] = 'طلب الدورة - الخطوة 1: مراجعة ض.د';
$string['status:course_workflow_step2'] = 'طلب الدورة - الخطوة 2: مراجعة ض.ق';
$string['status:course_workflow_step3'] = 'طلب الدورة - الخطوة 3: مراجعة ض.ق.خ';
$string['status:course_workflow_step4'] = 'طلب الدورة - الخطوة 4: مراجعة ر.د';

// خطوات سير العمل الافتراضي
$string['status:default_workflow_step1'] = 'سير العمل الافتراضي - الخطوة 1: مراجعة ض.د';
$string['status:default_workflow_step2'] = 'سير العمل الافتراضي - الخطوة 2: مراجعة ض.ق';
$string['status:default_workflow_step3'] = 'سير العمل الافتراضي - الخطوة 3: مراجعة ض.ق.خ';
$string['status:default_workflow_step4'] = 'سير العمل الافتراضي - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل الطلبات المالية
$string['status:finance_workflow_step1'] = 'الطلب المالي - الخطوة 1: مراجعة ض.د';
$string['status:finance_workflow_step2'] = 'الطلب المالي - الخطوة 2: مراجعة ض.ق';
$string['status:finance_workflow_step3'] = 'الطلب المالي - الخطوة 3: مراجعة ض.ق.خ';
$string['status:finance_workflow_step4'] = 'الطلب المالي - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل طلبات التقارير
$string['status:reports_workflow_step1'] = 'طلب التقرير - الخطوة 1: مراجعة ض.د';
$string['status:reports_workflow_step2'] = 'طلب التقرير - الخطوة 2: مراجعة ض.ق';
$string['status:reports_workflow_step3'] = 'طلب التقرير - الخطوة 3: مراجعة ر.د';

// خطوات سير عمل حجز الإقامة
$string['status:residence_workflow_step1'] = 'حجز الإقامة - الخطوة 1: مراجعة ض.د';
$string['status:residence_workflow_step2'] = 'حجز الإقامة - الخطوة 2: مراجعة ض.ق';
$string['status:residence_workflow_step3'] = 'حجز الإقامة - الخطوة 3: مراجعة ض.ق.خ';
$string['status:residence_workflow_step4'] = 'حجز الإقامة - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل طلبات التدريب
$string['status:training_workflow_step1'] = 'طلب التدريب - الخطوة 1: مراجعة ض.د';
$string['status:training_workflow_step2'] = 'طلب التدريب - الخطوة 2: مراجعة ض.ق';
$string['status:training_workflow_step3'] = 'طلب التدريب - الخطوة 3: مراجعة ض.ق.خ';
$string['status:training_workflow_step4'] = 'طلب التدريب - الخطوة 4: مراجعة ر.د';

// خطوات سير عمل إدارة المشاركين
$string['status:participants_workflow_step1'] = 'إدارة المشاركين - الخطوة 1: مراجعة ض.د';
$string['status:participants_workflow_step2'] = 'إدارة المشاركين - الخطوة 2: مراجعة ض.ق';
$string['status:participants_workflow_step3'] = 'إدارة المشاركين - الخطوة 3: مراجعة ض.ق.خ';
$string['status:participants_workflow_step4'] = 'إدارة المشاركين - الخطوة 4: مراجعة ر.د';

// Privacy
$string['privacy:metadata:local_status_history'] = 'سجلات انتقالات سير العمل';
$string['privacy:metadata:local_status_history:user_id'] = 'المستخدم الذي قام بالانتقال';
$string['privacy:metadata:local_status_history:note'] = 'الملاحظة المقدمة من المستخدم';
$string['privacy:metadata:local_status_history:ip_address'] = 'عنوان IP للمستخدم';
$string['privacy:metadata:local_status_history:user_agent'] = 'معلومات المتصفح';
$string['privacy:metadata:local_status_history:timecreated'] = 'وقت إجراء الانتقال';

// Settings
$string['settings'] = 'الإعدادات';
$string['enableaudit'] = 'تمكين سجل المراجعة';
$string['enableaudit_desc'] = 'تسجيل جميع انتقالات سير العمل لأغراض المراجعة';
$string['auditretentiondays'] = 'الاحتفاظ بالمراجعة (بالأيام)';
$string['auditretentiondays_desc'] = 'عدد الأيام للاحتفاظ بسجلات المراجعة (0 = الاحتفاظ إلى الأبد)';
$string['enablenotifications'] = 'تمكين الإشعارات';
$string['enablenotifications_desc'] = 'إرسال إشعارات بريد إلكتروني عند حدوث انتقالات سير العمل';

// Common workflow status terms
$string['pending'] = 'قيد الانتظار';
$string['submitted'] = 'تم التقديم';
$string['underreview'] = 'قيد المراجعة';
$string['approved'] = 'تم الإعتماد';
$string['rejected'] = 'مرفوض';
$string['cancelled'] = 'ملغى';

// Enhanced step management strings
$string['cannot_hide_critical_step'] = 'لا يمكن إخفاء الخطوات الابتدائية أو النهائية لأنها مهمة لسير العمل.';
$string['cannot_delete_critical_step'] = 'لا يمكن حذف الخطوات الابتدائية أو النهائية لأنها مهمة لسير العمل.';
$string['cannot_move_critical_step'] = 'لا يمكن نقل الخطوات الابتدائية أو النهائية لأن موقعها يديرها النظام تلقائياً.';
$string['cannot_modify_critical_step'] = 'لا يمكن تعديل الخطوات الابتدائية أو النهائية. يديرها النظام تلقائياً.';
$string['cannot_edit_protected'] = 'خطوة محمية - التعديل مقيد';
$string['cannot_delete_protected'] = 'خطوة محمية - لا يمكن حذفها';
$string['step_in_use'] = 'لا يمكن حذف الخطوة لأنها قيد الاستخدام حالياً من قبل مثيلات سير العمل.';

// Step protection and flags
$string['protected_step'] = 'هذه خطوة محمية';
$string['protected_step_warning'] = 'هذه خطوة محمية. الخطوات الابتدائية والنهائية يديرها النظام تلقائياً ولديها خيارات تعديل محدودة.';
$string['modifiable'] = 'قابل للتعديل';
$string['hidden'] = 'مخفي';
$string['stepposition'] = 'موقع الخطوة';
$string['single_step_workflow'] = 'هذه خطوة ابتدائية ونهائية معاً (سير عمل بخطوة واحدة)';
$string['initial_step_info'] = 'هذه الخطوة الابتدائية - تبدأ الطلبات هنا';
$string['final_step_info'] = 'هذه الخطوة النهائية - تنتهي الطلبات هنا';
$string['intermediate_step_info'] = 'هذه خطوة وسطية - يمكنك تعديلها أو إخفاؤها';

// Visual settings
$string['visualsettings'] = 'إعدادات بصرية';
$string['stepcolorhelp'] = 'رمز اللون السادس عشري لهذه الخطوة (مثل: #007bff). يستخدم في تصورات سير العمل.';
$string['stepiconhelp'] = 'فئة أيقونة FontAwesome لهذه الخطوة. تستخدم في عروض سير العمل.';
$string['stepactivehelp'] = 'الخطوات غير النشطة مخفية من تنفيذ سير العمل العادي ولكن يمكن إعادة تفعيلها لاحقاً.';

// Icon options
$string['noicon'] = 'بلا أيقونة';
$string['checkicon'] = 'علامة صح';
$string['clockicon'] = 'ساعة';
$string['usericon'] = 'مستخدم';
$string['usersicon'] = 'مستخدمون';
$string['cogicon'] = 'إعدادات';
$string['staricon'] = 'نجمة';
$string['flagicon'] = 'علم';

// Step actions
$string['hidestep'] = 'إخفاء الخطوة';
$string['showstep'] = 'إظهار الخطوة';
$string['confirmhidestep'] = 'هل أنت متأكد من أنك تريد إخفاء هذه الخطوة؟ لن تكون متاحة في مثيلات سير العمل الجديدة.';
$string['stephidden'] = 'تم إخفاء الخطوة بنجاح';
$string['stepshown'] = 'تم إظهار الخطوة بنجاح';

// Workflow structure display
$string['workflowstructure'] = 'هيكل سير العمل';
$string['totalsteps'] = 'إجمالي الخطوات';
$string['activesteps'] = 'الخطوات النشطة';
$string['actualworkflowsteps'] = 'خطوات سير العمل الفعلية';
$string['activeworkflowsteps'] = 'خطوات سير العمل النشطة';
$string['modifiablesteps'] = 'الخطوات القابلة للتعديل';
$string['showinactivesteps'] = 'إظهار الخطوات غير النشطة';
$string['hideinactivesteps'] = 'إخفاء الخطوات غير النشطة';
$string['systemflags_not_counted'] = 'ملاحظة: الخطوات الابتدائية والنهائية هي علامات نظام ولا تحسب كخطوات سير عمل.';

// Step counting clarification
$string['workflow_step_count_help'] = 'هذا العدد يستثني الخطوات الابتدائية والنهائية، والتي هي علامات يديرها النظام.';
$string['actual_steps_explanation'] = 'خطوات سير العمل الفعلية هي الخطوات الوسطية التي يتفاعل معها المستخدمون. الخطوات الابتدائية والنهائية هي مجرد علامات نظام.';
$string['system_managed_flags'] = 'علامات يديرها النظام (لا تحسب كخطوات)';

// Step management help
$string['stepmanagementhelp'] = 'دليل إدارة الخطوات';
$string['protectedstepshelp'] = 'الخطوات الابتدائية والنهائية يديرها النظام تلقائياً ولا يمكن حذفها أو نقلها.';
$string['modifiablestepshelp'] = 'الخطوات الوسطية يمكن تعديلها أو إخفاؤها أو حذفها أو إعادة ترتيبها حسب الحاجة.';
$string['hiddenstepshelp'] = 'الخطوات المخفية لا تستخدم في سير العمل الجديد ولكن المثيلات الموجودة تستمر بشكل طبيعي.';

// Reordering
$string['reordersteps'] = 'إعادة ترتيب الخطوات';
$string['dragdropsteps'] = 'اسحب وأفلت لإعادة ترتيب الخطوات';

// Protected step editing
$string['protected_step_updated'] = 'تم تحديث الخطوة المحمية بنجاح (تغييرات محدودة فقط)';
$string['protected_step_limited_editing'] = 'هذه خطوة محمية. يمكنك فقط تعديل أسماء العرض. الخصائص الأخرى يديرها النظام تلقائياً.';
$string['step_deactivation_info'] = 'إلغاء تفعيل الخطوة متاح';
$string['step_deactivation_help'] = 'يمكنك إلغاء تفعيل هذه الخطوة بإلغاء تحديد مربع "نشط" أدناه. الخطوات المعطلة مخفية من مثيلات سير العمل الجديدة ولكنها لا تؤثر على الموجودة.';

// Quick deactivation actions
$string['deactivate_step'] = 'إلغاء تفعيل الخطوة';
$string['activate_step'] = 'تفعيل الخطوة';
$string['step_deactivated'] = 'تم إلغاء تفعيل الخطوة بنجاح';
$string['step_activated'] = 'تم تفعيل الخطوة بنجاح';

// Additional actions section
$string['additionalactions'] = 'إجراءات إضافية';
$string['confirmdeactivatestep'] = 'هل أنت متأكد من أنك تريد إلغاء تفعيل هذه الخطوة؟ ستكون مخفية عن حالات سير العمل الجديدة.'; 