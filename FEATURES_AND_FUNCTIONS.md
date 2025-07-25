# EN NUR Membership System – Features & Functionality Guide

## Table of Contents
1. Introduction & Purpose
2. User Roles & Permissions
3. User Role Capabilities Matrix
4. Core Features (with Use Cases)
5. How to Perform Common Tasks
6. Notifications & Emails
7. Common Issues & Troubleshooting
8. Data Privacy & Security
9. Accessibility & Language
10. Mobile/Tablet Use
11. Logout & Session Management
12. Admin-Only Features
13. How to Update Terms or Policies
14. How to Delete/Deactivate a User
15. Glossary & FAQ
16. Support & Contact

---

## 1. Introduction & Purpose

This document explains what the EN NUR Membership System does, who can use it, and how to perform common tasks. It is written for both technical and non-technical users, including admins, staff, and members.

---

## 2. User Roles & Permissions

- **Super Admin:** Full access to all features, settings, and user management.
- **Admin:** Can manage users, payments, and memberships, but with some restrictions.
- **Member/User:** Can view and manage their own profile, payments, and membership status.

---

## 3. User Role Capabilities Matrix

| Feature/Action                | Super Admin | Admin | Member/User |
|-------------------------------|:-----------:|:-----:|:-----------:|
| Register/Login                |      ✓      |   ✓   |      ✓      |
| Change Own Password           |      ✓      |   ✓   |      ✓      |
| View Own Payments             |      ✓      |   ✓   |      ✓      |
| Pay Membership                |      ✓      |   ✓   |      ✓      |
| Export Own Receipts           |      ✓      |   ✓   |      ✓      |
| Update Own Profile            |      ✓      |   ✓   |      ✓      |
| Accept Terms                  |      ✓      |   ✓   |      ✓      |
| Create User                   |      ✓      |   ✓   |             |
| Create User w/o Email         |      ✓      |   ✓   |             |
| Edit Any User                 |      ✓      |   ✓   |             |
| Reset Any User Password       |      ✓      |   ✓   |             |
| Deactivate/Delete User        |      ✓      |   ✓   |             |
| Export All Payments           |      ✓      |   ✓   |             |
| Renew Any Membership          |      ✓      |   ✓   |             |
| Update Terms/Policies         |      ✓      |   ✓   |             |
| View System Logs              |      ✓      |   ✓   |             |
| Send Bulk Notifications       |      ✓      |   ✓   |             |
| Access Admin Dashboard        |      ✓      |   ✓   |             |

---

## 4. Core Features (with Use Cases & UI Descriptions)

### 4.1 User Registration & Login
- **What:** New users can register online with their email and personal details. Secure login with password. Email verification required.
- **Use Case:** A new member wants to join EN NUR and pay their membership online.
- **UI:** Registration and login forms are accessible from the homepage. After registration, a verification email is sent.

### 4.2 Change Password
- **What:** Any user can change their password from their profile page.
- **Use Case:** A member wants to update their password for security.
- **UI:** Click your name/profile in the top menu, select "Change Password", enter current and new password, and save.

### 4.3 Create New User (Admin/Super Admin)
- **What:** Admins can create new users (members) from the admin dashboard.
- **Use Case:** An admin registers a new member at the mosque who doesn’t have internet access.
- **UI:** In the admin dashboard, go to "Users" > "Create User", fill in details, assign a role, and save.

### 4.4 Membership Management & Renewals
- **What:** Members can view their membership status and renewal history. Automated reminders are sent before expiry. Renewal can be completed online.
- **Use Case:** A member receives a reminder and renews their membership before it expires.
- **UI:** Membership status and renewal options are shown on the user dashboard.

### 4.5 Payment Processing
- **What:** Multiple payment methods supported: Stripe (card), PayPal, TWINT (QR code), Bank Transfer.
- **Use Case:** A member pays their annual fee using their preferred method and receives a receipt.
- **UI:** Payments section lists all options. Each method has clear instructions and confirmation steps.

### 4.6 Admin Dashboard
- **What:** Overview of system status, payments, and memberships. Quick access to user management, payment exports, and logs.
- **Use Case:** An admin checks how many memberships are due for renewal this month.
- **UI:** Dashboard widgets and tables show key stats and quick links.

### 4.7 User Management (Admin/Super Admin)
- **What:** View, search, filter, edit, create, deactivate users. Reset passwords, assign roles.
- **Use Case:** An admin updates a user’s contact info or resets their password.
- **UI:** User list with search/filter, edit buttons, and action menus.

### 4.8 PDF Exports & Receipts
- **What:** Export payment receipts and reports as professional PDFs.
- **Use Case:** A member downloads a receipt for tax purposes; an admin exports all payments for accounting.
- **UI:** "Export PDF" buttons in payment history and admin reports.

### 4.9 Terms Acceptance
- **What:** New users must accept terms before using the system. Admins can update terms.
- **Use Case:** Terms are updated and all users must accept the new version on next login.
- **UI:** Terms acceptance prompt appears after registration or when terms change.

### 4.10 Notifications & Emails
- **What:** Automated emails for registration, password reset, payment confirmation, renewal reminders. Admins can send bulk notifications.
- **Use Case:** A member receives a payment confirmation email; an admin sends a notice to all users.
- **UI:** Email notifications are sent automatically. Admins use the dashboard to send bulk messages.

### 4.11 Security & Privacy (User Terms)
- **What:** Data is protected, passwords are secure, payments use trusted gateways, users control their info.
- **Use Case:** A user updates their privacy settings or requests data deletion.
- **UI:** Profile and privacy settings are accessible from the user menu.

### 4.12 Accessibility & Language
- **What:** The system uses clear fonts, high-contrast colors, and is screen-reader friendly. (If multilingual, mention supported languages.)
- **Use Case:** A visually impaired user navigates the site using a screen reader.
- **UI:** All forms and buttons are labeled for accessibility.

### 4.13 Mobile/Tablet Use
- **What:** The system is mobile-friendly and works on tablets and smartphones.
- **Use Case:** A member pays their fee from their phone.
- **UI:** Responsive design adapts to screen size; menus collapse for mobile.

### 4.14 Logout & Session Management
- **What:** Users can log out from the menu. Sessions time out after inactivity for security.
- **Use Case:** A user logs out after finishing their tasks; if inactive, they are logged out automatically.
- **UI:** "Logout" option in the user menu; session timeout message if inactive.

### 4.15 Admin-Only Features
- **What:** View system logs, export all user data, manage terms, send bulk notifications, advanced user/payment management.
- **Use Case:** An admin exports all user data for a compliance audit.
- **UI:** Admin dashboard has exclusive sections for these features.

### 4.16 Update Terms or Policies (Admin)
- **What:** Admins can update the terms of service or privacy policy. Users must accept new terms on next login.
- **Use Case:** The organization updates its privacy policy and needs all users to accept it.
- **UI:** Admins edit terms in the dashboard; users see a prompt to accept changes.

### 4.17 Delete/Deactivate a User (Admin)
- **What:** Admins can deactivate or delete users. Deactivated users lose access but payment history is retained.
- **Use Case:** A member leaves the organization and their account is deactivated.
- **UI:** In the user list, select "Deactivate" or "Delete" from the action menu.

### 4.18 Create a User Without Email Verification (Direct Password Setup)
- **What:** Admins can create a user and set the password directly, bypassing email verification. Useful for users without email or for quick onboarding.
- **Use Case:** An admin creates an account for a member who does not use email.
- **UI:** "Create User Without Email" option in the admin user creation form.

---

## 5. How to Perform Common Tasks

### 4.1 Register as a New Member
1. Go to the registration page.
2. Fill in your details and submit.
3. Check your email for a verification link.
4. Click the link to activate your account.
5. Log in and complete your profile.

### 4.2 Log In
1. Go to the login page.
2. Enter your email and password.
3. Click "Log In".

### 4.3 Reset Forgotten Password
1. On the login page, click "Forgot Password?"
2. Enter your email address.
3. Check your email for a reset link.
4. Click the link and set a new password.

### 4.4 Change Your Password
1. Log in and go to your profile.
2. Click "Change Password".
3. Enter your current and new password.
4. Save changes.

### 4.5 Update Your Profile
1. Log in and go to your profile page.
2. Edit your details (name, contact info, etc.).
3. Save changes.

### 4.6 Pay Membership Fee
1. Log in and go to the payments section.
2. Select your preferred payment method (Stripe, PayPal, TWINT, Bank Transfer).
3. Follow the instructions to complete payment.
4. Download your receipt if needed.

### 4.7 Export a Payment Receipt (PDF)
1. Go to your payment history.
2. Find the payment you want a receipt for.
3. Click "Export PDF".
4. Download or print the receipt.

### 4.8 Admin: Create a New User
1. Log in as Admin or Super Admin.
2. Go to the "Users" section.
3. Click "Create User".
4. Fill in the user’s details and assign a role.
5. Save. The user will receive an email to activate their account.

### 4.9 Admin: Export All Payments
1. Go to the admin dashboard.
2. Click on "Export Payments".
3. Choose the date range or filters.
4. Download the PDF or Excel report.

### 4.10 Admin: Renew a Member’s Membership
1. Go to the user’s profile in the admin dashboard.
2. Click "Renew Membership".
3. Process payment or mark as paid.

### 4.11 Admin: Create a User Without Email Verification (Direct Password Setup)
Admins can create a user account and set the password directly, bypassing email verification. This is useful for users who do not have an email address or need immediate access.

**Steps:**
1. Log in as Admin or Super Admin.
2. Go to the "Users" section.
3. Click "Create User Without Email" (or similar option).
4. Fill in the user’s details (name, username, etc.).
5. Set a password for the user directly.
6. Save. The user can now log in immediately using the provided credentials—no email verification required.

**Note:**
- This method is intended for special cases (e.g., users without email, quick onboarding at the mosque/office).
- The admin should communicate the username and password securely to the new user.

---

## 6. Notifications & Emails

- **Registration:** Welcome email with verification link.
- **Password Reset:** Email with reset instructions.
- **Payment Confirmation:** Email receipt after successful payment.
- **Membership Renewal Reminder:** Automated reminder before expiry.
- **Bulk Notifications:** Admins can send announcements to all or selected users.
- **Other:** Admin notifications for system events (e.g., failed payments).

---

## 7. Common Issues & Troubleshooting

- **Didn’t receive verification email:** Check spam folder; request a new link from the login page.
- **Payment failed:** Try another method; check card/bank details; contact support if needed.
- **Can’t log in:** Use "Forgot Password"; ensure email is correct; contact admin if locked out.
- **Session timed out:** Log in again; sessions expire after inactivity for security.
- **Can’t find a feature:** Use the dashboard search or contact support.

---

## 8. Data Privacy & Security

- Your data is stored securely and only accessible to authorized users.
- Passwords are encrypted and never visible to anyone.
- Payments are processed via secure, PCI-compliant gateways.
- You can request to update or delete your data by contacting the admin team.
- The system complies with relevant privacy laws and best practices.

---

## 9. Accessibility & Language

- The system is designed for accessibility (screen readers, keyboard navigation, high-contrast colors).
- (If multilingual, list supported languages here.)
- Feedback on accessibility is welcome—contact support to suggest improvements.

---

## 10. Mobile/Tablet Use

- The system works on all modern smartphones and tablets.
- All features are accessible on mobile, with responsive layouts.
- For best experience, use the latest version of your browser.

---

## 11. Logout & Session Management

- Click your name/profile in the top menu and select "Logout".
- For security, you will be logged out automatically after a period of inactivity.
- If you see a session timeout message, simply log in again.

---

## 12. Admin-Only Features

- View and export system logs.
- Export all user or payment data.
- Manage terms and privacy policies.
- Send bulk notifications.
- Advanced user and payment management tools.

---

## 13. How to Update Terms or Policies

- Admins can edit the terms of service or privacy policy from the admin dashboard.
- After updating, all users will be prompted to accept the new terms on their next login.
- Keep terms clear and up to date for legal compliance.

---

## 14. How to Delete/Deactivate a User

- In the admin dashboard, go to the user list.
- Select the user and choose "Deactivate" or "Delete" from the action menu.
- Deactivated users cannot log in but their payment history is retained.
- Deleted users are removed from the system (use with caution).

---

## 15. Glossary & FAQ

**Glossary:**
- **Member:** A registered user of the organization.
- **Admin:** A user with management privileges.
- **Super Admin:** The highest-level admin with full access.
- **Payment Gateway:** A service for processing online payments (Stripe, PayPal, etc.).
- **PDF Receipt:** A downloadable, printable proof of payment.
- **TWINT:** A Swiss mobile payment system.
- **Terms Acceptance:** Agreement to the organization’s rules and privacy policy.
- **Dashboard:** The main page showing your account and system status.
- **Export:** Downloading data (e.g., payments) as a file (PDF, Excel).
- **Renewal:** Extending your membership for another period.

**FAQ:**
- **Q: How do I reset my password?**
  A: Click "Forgot Password" on the login page and follow the instructions.
- **Q: Can I pay for someone else’s membership?**
  A: Yes, admins can assign payments to users; contact the admin for help.
- **Q: What if I don’t have an email address?**
  A: An admin can create your account directly and set a password for you.
- **Q: How do I get a payment receipt?**
  A: Go to your payment history and click "Export PDF" next to the payment.
- **Q: Who can see my data?**
  A: Only authorized admins and yourself; your data is private and secure.
- **Q: How do I contact support?**
  A: See the Support & Contact section below.

---

## 16. Support & Contact

- For help, contact the admin team at: info@xhamia-en-nur.ch
- For technical issues, refer to the documentation or contact your system administrator.
- To report a bug or request a feature, email support or use the feedback form (if available).

---

*This guide is designed to help all users understand and use the EN NUR Membership System effectively. For more details, see the README or technical documentation.* 