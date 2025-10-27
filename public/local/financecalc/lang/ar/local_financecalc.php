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
 * Language strings for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'تقرير الإحصائيات المالية';
$string['financecalc'] = 'تقرير الإحصائيات المالية';
$string['financecalc:view'] = 'عرض الحسابات المالية';
$string['financecalc:manage'] = 'إدارة الحسابات المالية';

// Navigation and pages.
$string['financialreport'] = 'تقرير ميزانية الخطط السنوية';
$string['financialoverview'] = 'ميزانية الخطط السنوية';

// Report columns.
$string['year'] = 'سنة الخطة السنوية';
$string['spending'] = 'الإنفاق (ريال عماني)';
$string['budget'] = 'الميزانية (ريال عماني)';
$string['balance'] = 'الرصيد (ريال عماني)';
$string['spending_finance'] = 'إنفاق الخدمات المالية';
$string['spending_participant'] = 'إنفاق المشاركين';

// Filters.
$string['filter_year'] = 'تصفية حسب السنة';
$string['filter_all_years'] = 'جميع السنوات';
$string['filter_approved_only'] = 'الطلبات المعتمدة فقط';

// Messages.
$string['no_data_available'] = 'لا توجد بيانات مالية متاحة للمعايير المحددة.';
$string['data_last_updated'] = 'آخر تحديث للبيانات: {$a}';
$string['refresh_data'] = 'تحديث البيانات';
$string['refresh_data_success'] = 'تم تحديث البيانات المالية بنجاح.';
$string['refresh_data_live'] = 'تم تحديث البيانات (وضع الحساب المباشر)';
$string['refresh_data_error'] = 'خطأ في تحديث البيانات المالية.';

// Scheduled task.
$string['task_refresh_financial_data'] = 'تحديث بيانات الحساب المالي';
$string['task_refresh_financial_data_desc'] = 'تحديث البيانات المالية المخزنة مؤقتاً من الخدمات المالية وطلبات المشاركين.';

// Errors.
$string['error_no_permission'] = 'ليس لديك صلاحية لعرض هذه الصفحة.';
$string['error_invalid_year'] = 'سنة غير صحيحة محددة.';

// Filter.
$string['filter'] = 'تصفية';

// Clause spending report
$string['clause_spending_report'] = 'تقرير إنفاق البنود';
$string['clause_spending_overview'] = 'نظرة عامة على إنفاق البنود';
$string['clause_name'] = 'اسم البند';
$string['budget_amount'] = 'مبلغ الميزانية';
$string['spent_amount'] = 'المبلغ المنفق';
$string['remaining_budget'] = 'الميزانية المتبقية';
$string['spending_percentage'] = 'نسبة الإنفاق %';
$string['request_count'] = 'عدد الطلبات';
$string['actions'] = 'الإجراءات';
$string['view_details'] = 'عرض التفاصيل';
$string['back_to_clause_list'] = 'العودة إلى قائمة البنود';
$string['total_clauses'] = 'إجمالي البنود';
$string['total_budget'] = 'إجمالي الميزانية';
$string['total_spent'] = 'إجمالي المنفق';
$string['approved_spent'] = 'المنفق المعتمد';
$string['spending_records'] = 'سجلات الإنفاق';
$string['request_id'] = 'معرف الطلب';
$string['requester'] = 'مقدم الطلب';
$string['amount'] = 'المبلغ';
$string['status'] = 'الحالة';
$string['request_date'] = 'تاريخ الطلب';
$string['notes'] = 'ملاحظات';
$string['no_clauses_found'] = 'لم يتم العثور على بنود للمعايير المحددة.';
$string['no_spending_records'] = 'لم يتم العثور على سجلات إنفاق لهذا البند.';
$string['show_hidden_clauses'] = 'إظهار البنود المخفية';
$string['hide_hidden_clauses'] = 'إخفاء البنود المخفية';
$string['clause_year'] = 'سنة البند';
$string['course'] = 'المقرر';

// About section
$string['about_finance_calculator'] = 'حول تقرير الحسابات المالية';
$string['about_finance_calculator_desc'] = 'يوفر تقرير الحسابات المالية أدوات شاملة للتحليل المالي وإعداد التقارير لتتبع الإنفاق عبر مصادر التمويل والبنود المختلفة. استخدم الأدوات أعلاه للوصول إلى التقارير المالية التفصيلية وتحليل الإنفاق.';

// Navigation and UI
$string['navigation_filters'] = 'التنقل والمرشحات';
$string['filter_options'] = 'خيارات التصفية';
$string['dashboard'] = 'لوحة التحكم';
$string['comprehensive_financial_analysis'] = 'لوحة تحليل وإعداد تقارير ميزانية الخطط السنوية';
$string['detailed_spending_analysis'] = 'تحليل مفصل للإنفاق حسب البنود والفئات المالية';

// Index page
    $string['welcome_to_finance_calculator'] = 'مرحباً بك في تقرير الحسابات المالية';
$string['choose_report_type'] = 'اختر نوع التقرير المالي الذي تريد عرضه';
$string['clause_spending_description'] = 'عرض تحليل مفصل للإنفاق لجميع البنود والفئات المالية';
$string['financial_overview_description'] = 'عرض بيانات ميزانية الخطط السنوية وتحليل الإنفاق حسب السنة';