# Security Audit Report for OJS Developer Website

**Date:** April 8, 2026
**Auditor:** Manus AI

## Executive Summary

This report details a security audit conducted on the OJS Developer Website codebase, specifically focusing on the `PEPEXOJS` repository cloned into `websitefix.snada`. The audit aimed to identify potential vulnerabilities, assess existing security measures, and recommend improvements. Overall, the codebase demonstrates a good foundation for security, utilizing prepared statements for database interactions, CSRF protection, and strong password hashing. However, some areas for improvement were identified, particularly concerning sensitive data storage and public data exposure.

## Key Findings and Recommendations

### 1. Sensitive Data Storage (WhatsApp API Token)

**Finding:** The WhatsApp API token (`wa_api_token`) is stored in plain text within the `settings` table in the database. While access to the database is typically restricted, storing API keys in plain text increases the risk of compromise if the database is breached.

**Recommendation:** Implement a more secure method for storing sensitive API keys. Consider the following options:
*   **Environment Variables:** Store the API token as an environment variable on the server. This prevents the token from being committed to version control or directly exposed in the database.
*   **Secrets Management Service:** For more complex deployments, utilize a dedicated secrets management service (e.g., HashiCorp Vault, AWS Secrets Manager, Azure Key Vault).
*   **Encryption:** Encrypt the token before storing it in the database and decrypt it only when needed. This adds a layer of protection, though key management for encryption would also need to be secure.

### 2. Public Data Exposure (Tracking Page)

**Finding:** The `/pages/tracking.php` endpoint allows unauthenticated users to retrieve order details (client name, institution, service, package, status, timestamps, milestones) by providing a valid `tracking_code`. While this functionality is intended, it could be considered a privacy concern if the exposed data is deemed sensitive by clients.

**Recommendation:** Assess the sensitivity of the data exposed through the tracking page. If client privacy is a high concern, consider:
*   **Data Minimization:** Only expose essential information required for tracking, omitting potentially sensitive client details.
*   **Rate Limiting:** Implement rate limiting on the tracking endpoint to prevent automated scraping or brute-force attacks on tracking codes.
*   **Partial Obfuscation:** Partially obfuscate sensitive client information (e.g., `client_name` as `C***t Name`).

### 3. File Upload Validation Inconsistency (Fixed)

**Finding:** In `admin/pengaturan.php`, the initial extension check for logo uploads (`if (!in_array($ext, ['jpg','jpeg','png','gif','webp','svg']))`) allowed SVG files, but the underlying `uploadImage()` function (in `includes/functions.php`) explicitly disallowed SVG due to XSS risks. This inconsistency would lead to failed SVG uploads despite the initial check passing.

**Resolution:** The code has been updated to remove `svg` from the allowed extensions in `admin/pengaturan.php`, aligning it with the stricter validation in `uploadImage()`. This prevents misleading error messages and ensures consistent security for file uploads.

### 4. Robust Database Interactions

**Finding:** The application consistently uses PDO with prepared statements for all database queries (`query`, `fetch`, `fetchAll`, `insert`, `update`, `delete` functions). This effectively mitigates SQL injection vulnerabilities.

**Recommendation:** Continue to enforce the use of prepared statements for all database interactions.

### 5. Cross-Site Request Forgery (CSRF) Protection

**Finding:** CSRF tokens are generated and verified for POST requests, particularly in admin forms and public forms like the consultation page. This protects against CSRF attacks.

**Recommendation:** Ensure all forms submitting POST requests have appropriate CSRF protection.

### 6. Secure Password Hashing

**Finding:** Admin passwords are hashed using `password_hash()` with `PASSWORD_BCRYPT`, which is a strong and recommended hashing algorithm.

**Recommendation:** Maintain the use of strong, modern password hashing algorithms.

### 7. Session Hardening

**Finding:** Session cookies are configured with `secure`, `httponly`, and `samesite` attributes, enhancing session security against various attacks like XSS and CSRF.

**Recommendation:** Continue to apply these session hardening measures.

## Conclusion

The OJS Developer Website has a solid foundation for security. The primary areas for immediate improvement involve enhancing the storage of sensitive API tokens and carefully reviewing the public data exposure on the tracking page. The identified file upload inconsistency has been addressed. Implementing the recommended changes will further strengthen the application's security posture.
