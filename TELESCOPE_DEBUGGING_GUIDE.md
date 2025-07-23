# 🔍 Laravel Telescope - Debugging Guide for EN NUR Membership System

## 📋 **Overview**

Laravel Telescope is now integrated into your membership application for advanced debugging, monitoring, and performance analysis. This guide explains how to use Telescope specifically for your payment processing and membership management system.

## 🚀 **Getting Started**

### **Access Telescope**
- **URL**: `http://localhost:8000/admin/telescope` (Development)
- **Production**: `https://yourdomain.com/admin/telescope`
- **Access**: Only available to Admin and Super Admin users
- **Navigation**: Click "🔍 Telescope" in the admin navigation bar

### **Authentication**
- **Local Environment**: Any authenticated user can access
- **Production/Staging**: Only users with `admin` or `super_admin` roles
- **Security**: Sensitive payment data is automatically hidden

---

## 🏷️ **Smart Tagging System**

Telescope automatically tags operations for easy filtering:

### **Payment Operations** (`payment` tag)
- All Stripe, PayPal, TWINT, and Bank Transfer processing
- Webhook handling from payment gateways
- Payment status updates and confirmations

### **Admin Operations** (`admin` tag)
- User management actions
- Payment status changes
- System administration tasks

### **Membership Operations** (`membership` tag)
- Membership renewals and expiry tracking
- Renewal notification emails
- Membership status calculations

---

## 🔧 **Key Features for Your Application**

### **1. Payment Processing Monitoring**

**What to Monitor:**
- **Stripe Sessions**: Check session creation and completion
- **Webhook Processing**: Verify webhook delivery and processing
- **Payment Failures**: Track failed payments and error reasons
- **API Response Times**: Monitor external API performance

**How to Use:**
1. Go to **Requests** tab
2. Filter by `/payment` or `/webhook` paths
3. Click on any request to see full details
4. Check **Response** tab for API responses
5. Use **Queries** tab to see database operations

### **2. Database Performance**

**Query Monitoring:**
- **Slow Queries**: Automatically highlights queries >50ms
- **Payment Queries**: Track payment and user lookups
- **Membership Calculations**: Monitor renewal calculations
- **N+1 Problems**: Identify inefficient queries

**How to Use:**
1. Go to **Queries** tab
2. Sort by **Duration** to find slow queries
3. Look for **repeated queries** (N+1 indicators)
4. Focus on `payments`, `users`, and `membership_renewals` tables

### **3. Email Monitoring**

**Track All Emails:**
- **Payment Confirmations**: Receipt emails to users
- **Membership Renewals**: Renewal reminder emails
- **Admin Notifications**: System alerts to admins

**How to Use:**
1. Go to **Mail** tab
2. Click on any email to see full content
3. Check **Recipients** and **Status**
4. View HTML and Text versions

### **4. Exception Tracking**

**Critical for Payment System:**
- **Payment Failures**: Stripe/PayPal API errors
- **Webhook Errors**: Failed webhook processing
- **Database Errors**: Data integrity issues
- **Email Failures**: Failed notification delivery

**How to Use:**
1. Go to **Exceptions** tab
2. Focus on **HIGH PRIORITY** exceptions
3. Check **Stack Trace** for debugging
4. Look for patterns in error frequency

---

## 🎯 **Debugging Specific Scenarios**

### **Payment Processing Issues**

**Scenario 1: Stripe Payment Not Completing**
1. **Requests** → Filter by `stripe`
2. Check Stripe session creation request
3. Look for webhook delivery in **Requests**
4. Verify database updates in **Queries**
5. Check for exceptions in **Exceptions**

**Scenario 2: PayPal Integration Problems**
1. **Client Requests** → Look for PayPal API calls
2. Check response status codes
3. **Queries** → Verify payment record creation
4. **Logs** → Look for PayPal-specific errors

**Scenario 3: Webhook Failures**
1. **Requests** → Filter by `/webhook`
2. Check HTTP status codes (should be 200)
3. **Exceptions** → Look for webhook processing errors
4. **Queries** → Verify payment status updates

### **Membership System Issues**

**Scenario 1: Renewal Notifications Not Sending**
1. **Schedule** → Check if `membership:check-renewals` is running
2. **Mail** → Verify emails are being sent
3. **Queries** → Check membership renewal queries
4. **Exceptions** → Look for email sending failures

**Scenario 2: Membership Status Calculation**
1. **Requests** → Admin dashboard request
2. **Queries** → Look for membership calculation queries
3. **Models** → Check User and MembershipRenewal operations
4. Filter by `membership` tag

### **Performance Issues**

**Scenario 1: Slow Dashboard Loading**
1. **Requests** → Find dashboard request
2. **Queries** → Look for slow queries (>50ms)
3. Check for N+1 queries on user relationships
4. **Views** → See view rendering time

**Scenario 2: Payment Page Delays**
1. **Requests** → Payment creation requests
2. **Client Requests** → External API calls
3. **Queries** → Database performance
4. Look for bottlenecks in processing chain

---

## 📊 **Monitoring Best Practices**

### **Daily Monitoring Checklist**

**For Admins:**
- [ ] Check **Exceptions** for any payment errors
- [ ] Review **Mail** tab for failed email deliveries
- [ ] Monitor **Queries** for performance issues
- [ ] Verify **Schedule** tasks are running properly

**For Payment Issues:**
- [ ] Filter requests by `payment` tag
- [ ] Check webhook delivery success rates
- [ ] Monitor external API response times
- [ ] Review failed payment patterns

### **Performance Optimization**

**Query Optimization:**
1. **Identify slow queries** (>50ms threshold)
2. **Look for N+1 problems** in user/payment relationships
3. **Add database indexes** for frequently queried fields
4. **Use eager loading** for related models

**API Performance:**
1. **Monitor external API response times** (Stripe, PayPal)
2. **Set up retry mechanisms** for failed API calls
3. **Cache frequently accessed data**
4. **Implement timeout handling**

---

## 🔒 **Security & Privacy**

### **Data Protection**
Telescope automatically hides sensitive information:
- **Payment card details**
- **API keys and secrets**
- **User passwords**
- **Banking information**
- **Personal identification data**

### **Production Considerations**
- **Limited data retention** (configured for important events only)
- **Admin-only access** enforced via middleware
- **Sensitive request parameters** are hidden
- **Webhook signatures** are protected

---

## 🛠️ **Configuration**

### **Environment Variables**
```env
# Enable/disable Telescope
TELESCOPE_ENABLED=true

# Custom path (default: admin/telescope)
TELESCOPE_PATH=admin/telescope

# Log levels (debug, info, warning, error)
TELESCOPE_LOG_LEVEL=warning

# Slow query threshold (milliseconds)
TELESCOPE_SLOW_QUERY_THRESHOLD=50
```

### **Production Settings**
- **Disabled by default** in production
- **Admin authentication** required
- **Filtered to important events** only
- **Limited data retention**

---

## 🚨 **Troubleshooting**

### **Common Issues**

**1. Can't Access Telescope**
- Check user role (must be admin/super_admin)
- Verify TELESCOPE_ENABLED=true in .env
- Clear cache: `php artisan config:clear`

**2. No Data Showing**
- Check watchers are enabled in config
- Verify database tables exist
- Run: `php artisan telescope:install`

**3. Performance Impact**
- Disable unnecessary watchers
- Adjust slow query threshold
- Use filtering to reduce data volume

### **Emergency Disable**
If Telescope causes issues:
```bash
# Disable via environment
TELESCOPE_ENABLED=false

# Or disable via config
php artisan config:cache
```

---

## 📚 **Advanced Usage**

### **Custom Filters**
Create custom filters for specific debugging needs:
```php
// In TelescopeServiceProvider
Telescope::filter(function (IncomingEntry $entry) {
    // Custom filtering logic
    return $entry->hasTag('payment') && $entry->isException();
});
```

### **Performance Monitoring**
Set up alerts for:
- **Slow queries** (>100ms)
- **High exception rates**
- **Failed payment webhooks**
- **Email delivery failures**

---

## 📞 **Support**

### **Getting Help**
1. **Check Telescope first** - Most issues are visible in the interface
2. **Use tags to filter** - Focus on relevant operations
3. **Check multiple tabs** - Requests, Queries, Exceptions, Mail
4. **Look for patterns** - Repeated issues indicate systemic problems

### **Reporting Issues**
When reporting problems, include:
- **Telescope request ID**
- **Relevant exception details**
- **Database query logs**
- **Timeline of events**

---

**🎯 Telescope is your debugging companion for the EN NUR membership system. Use it to monitor payments, track performance, and ensure smooth operation of your community platform!** 