
# 🗃️ Oracle Database Schema & Dummy Data

## 🏗️ **Database Schema**

### **Table 1: DUNIA_EMPLOYEES**
```sql
CREATE TABLE dunia_employees (
    pf_number                VARCHAR2(20)   PRIMARY KEY,
    prs_name1_a              VARCHAR2(100),
    prs_name2_a              VARCHAR2(100),
    prs_name3_a              VARCHAR2(100),
    prs_tribe_a              VARCHAR2(100),
    aaa_emp_civil_number_ar  VARCHAR2(20)   -- Format: "C-12345"
);
```

### **Table 2: DUNIA_PERSONAL_DETAILS**
```sql
CREATE TABLE dunia_personal_details (
    civil_number         NUMBER        PRIMARY KEY,
    passport_number      VARCHAR2(20),
    name_arabic_1        VARCHAR2(100),
    name_arabic_2        VARCHAR2(100),
    name_arabic_3        VARCHAR2(100),
    name_arabic_6        VARCHAR2(100),
    nationality_arabic_SYS VARCHAR2(100)   -- Nationality in Arabic
);
```

### **🔗 Relationship**
- **DUNIA_EMPLOYEES.aaa_emp_civil_number_ar** (VARCHAR2 - "C-10001") 
- **↔** 
- **DUNIA_PERSONAL_DETAILS.civil_number** (NUMBER - 10001)
- **JOIN Logic:** `REGEXP_SUBSTR(e.AAA_EMP_CIVIL_NUMBER_AR, '[0-9]+') = TO_CHAR(p.CIVIL_NUMBER)`

---

## 📊 **Test Data**

### **1. Insert into `DUNIA_PERSONAL_DETAILS`**

```sql
INSERT ALL
  INTO dunia_personal_details VALUES (10001, 'P-A001', 'أحمد', 'محمد', 'علي', 'الهاشمي', 'عماني')
  INTO dunia_personal_details VALUES (10002, 'P-A002', 'سارة', 'خالد', 'سليمان', 'العتيبي', 'عماني')
  INTO dunia_personal_details VALUES (10003, 'P-A003', 'خالد', 'جابر', 'سعد', 'الدوسري', 'عماني')
  INTO dunia_personal_details VALUES (10004, 'P-A004', 'ليلى', 'ناصر', 'علي', 'الشمراني', 'عماني')
  INTO dunia_personal_details VALUES (10005, 'P-A005', 'عمر',  'سعود', 'حسن', 'القحطاني', 'عماني')
  INTO dunia_personal_details VALUES (10006, 'P-A006', 'منى',  'فهد',  'ماجد', 'الشمري', 'كويتي')
  INTO dunia_personal_details VALUES (10007, 'P-A007', 'يوسف', 'تركي', 'بدر', 'الغامدي', 'عماني')
  INTO dunia_personal_details VALUES (10008, 'P-A008', 'ريم',  'سامي', 'صالح', 'الحربي', 'سعودي')
  INTO dunia_personal_details VALUES (10009, 'P-A009', 'مازن', 'راشد', 'عبدالرحمن', 'الغنام', 'عماني')
  INTO dunia_personal_details VALUES (10010, 'P-A010', 'هناء', 'فارس', 'طارق', 'المطيري', 'بحريني')
  INTO dunia_personal_details VALUES (20001, 'P-B001', 'زياد', 'أحمد', 'فهد', 'الفيصل', 'عماني')
  INTO dunia_personal_details VALUES (20002, 'P-B002', 'أسماء', 'عبدالله', 'محمد', 'الخالد', 'قطري')
  INTO dunia_personal_details VALUES (20003, 'P-B003', 'طلال', 'سليمان', 'عبدالرحمن', 'المنصور', 'عماني')
  INTO dunia_personal_details VALUES (20004, 'P-B004', 'رند', 'فيصل', 'تركي', 'البندر', 'عماني')
  INTO dunia_personal_details VALUES (20005, 'P-B005', 'بدر', 'خالد', 'عمر', 'السعود', 'عماني')
  INTO dunia_personal_details VALUES (20006, 'P-B006', 'دانة', 'محمد', 'إبراهيم', 'الراجح', 'أردني')
  INTO dunia_personal_details VALUES (20007, 'P-B007', 'وليد', 'عبدالعزيز', 'صالح', 'المبارك', 'عماني')
  INTO dunia_personal_details VALUES (20008, 'P-B008', 'لمى', 'ناصر', 'فهد', 'السديري', 'لبناني')
  INTO dunia_personal_details VALUES (20009, 'P-B009', 'فهد', 'عبدالرحمن', 'سعد', 'الثاني', 'عماني')
  INTO dunia_personal_details VALUES (20010, 'P-B010', 'شهد', 'سامي', 'عمر', 'القاسم', 'مصري')
  INTO dunia_personal_details VALUES (20100, '١٢٣٤٥٦', 'سلطان', 'محمد', 'عبدالعزيز', 'الراشدي', 'عماني');
  INTO dunia_personal_details VALUES (20101, '١٢٣٤٥P', 'أحمد', 'محمد', 'عبدالعزيز', 'أحمد', 'أردني');
SELECT * FROM dual;
```

---

### **2. Insert into `DUNIA_EMPLOYEES`**

```sql
INSERT ALL
  INTO dunia_employees VALUES ('PF001', 'أحمد', 'محمد', 'علي', 'الهاشمي', 'C-10001')
  INTO dunia_employees VALUES ('PF002', 'سارة', 'خالد', 'سليمان', 'العتيبي', 'C-10002')
  INTO dunia_employees VALUES ('PF003', 'خالد', 'جابر', 'سعد', 'الدوسري', 'C-10003')
  INTO dunia_employees VALUES ('PF004', 'ليلى', 'ناصر', 'علي', 'الشمراني', 'C-10004')
  INTO dunia_employees VALUES ('PF005', 'عمر', 'سعود', 'حسن', 'القحطاني', 'C-10005')
  INTO dunia_employees VALUES ('PF006', 'منى', 'فهد', 'ماجد', 'الشمري', 'C-10006')
  INTO dunia_employees VALUES ('PF007', 'يوسف', 'تركي', 'بدر', 'الغامدي', 'C-10007')
  INTO dunia_employees VALUES ('PF008', 'ريم', 'سامي', 'صالح', 'الحربي', 'C-10008')
  INTO dunia_employees VALUES ('PF009', 'مازن', 'راشد', 'عبدالرحمن', 'الغنام', 'C-10009')
  INTO dunia_employees VALUES ('PF010', 'هناء', 'فارس', 'طارق', 'المطيري', 'C-10010')
  INTO dunia_employees VALUES ('PF011', 'محمد', 'عبدالله', 'أحمد', 'البقمي', 'C-10011')
  INTO dunia_employees VALUES ('PF012', 'فاطمة', 'صالح', 'محمد', 'الزهراني', 'C-10012')
  INTO dunia_employees VALUES ('PF013', 'عبدالرحمن', 'إبراهيم', 'سليمان', 'العنزي', 'C-10013')
  INTO dunia_employees VALUES ('PF014', 'نورا', 'عمر', 'فيصل', 'المالكي', 'C-10014')
  INTO dunia_employees VALUES ('PF015', 'سلطان', 'محمد', 'عبدالعزيز', 'الراشد', 'C-10015')
SELECT * FROM dual;
```
