# Referral System Testing Checklist
**© Atia Hegazy — atiaeno.com**

## 🎯 Critical Money-Related Tests - Must Pass 100%

### 1. Registration Flow Tests
- [ ] **Valid Referral Code Registration**
  - Test: Register new user with valid referral code
  - Expected: User created, linked to referrer, no errors
  - Verify: `users.referred_by_affiliate_id` is set correctly

- [ ] **Invalid Referral Code Registration**
  - Test: Register with non-existent referral code
  - Expected: Validation error, user not created
  - Verify: Error message shown, no user created

- [ ] **Inactive Referral Code Registration**
  - Test: Register with inactive affiliate's referral code
  - Expected: Validation error, user not created
  - Verify: Error message shown, no user created

- [ ] **No Referral Code Registration**
  - Test: Register without referral code
  - Expected: User created normally
  - Verify: `referred_by_affiliate_id` is null

- [ ] **Case-Insensitive Referral Code**
  - Test: Register with referral code in different case
  - Expected: User created, linked correctly
  - Verify: Case doesn't affect matching

### 2. Payout System Tests
- [ ] **Payout with Referral Earnings**
  - Test: Request payout when user has both direct and referral earnings
  - Expected: Payout created with total amount (direct + referral)
  - Verify: Payout amount = pending_earnings + referral_pending_earnings

- [ ] **Payout Deducts Both Earnings**
  - Test: After payout request, check affiliate balances
  - Expected: Both pending_earnings and referral_pending_earnings set to 0
  - Verify: No pending earnings remain

- [ ] **Minimum Payout with Referrals**
  - Test: Request payout below minimum (including referral earnings)
  - Expected: Payout rejected
  - Verify: Error message, no payout created

- [ ] **Duplicate Payout Prevention**
  - Test: Request second payout while first is pending
  - Expected: Second request rejected
  - Verify: Only one pending payout exists

### 3. Commission Calculation Tests
- [ ] **Daily Commission Calculation**
  - Test: Run SyncReferralCommissionsJob for a date
  - Expected: Referral commissions calculated and logged
  - Verify: Referral earnings updated, commission records created

- [ ] **Commission Rate Application**
  - Test: Set commission rate to 2%, verify calculations
  - Expected: Correct commission amount calculated
  - Verify: Commission amount = referral_earnings * rate/100

- [ ] **Zero Earnings Handling**
  - Test: Run job when referral has zero earnings
  - Expected: No commissions created
  - Verify: No unnecessary commission records

- [ ] **Inactive Affiliate Handling**
  - Test: Run job with inactive referrer or referral
  - Expected: No commissions created
  - Verify: Inactive affiliates skipped

### 4. Dashboard Display Tests
- [ ] **Referral Earnings Display**
  - Test: View affiliate dashboard with referral earnings
  - Expected: Referral earnings shown correctly
  - Verify: Dashboard shows separate referral earnings

- [ ] **Referred Users Count**
  - Test: Dashboard shows count of referred users
  - Expected: Accurate count displayed
  - Verify: Only active referred affiliates counted

- [ ] **Total Earnings Calculation**
  - Test: Dashboard shows total including referrals
  - Expected: Total = direct_earnings + referral_earnings
  - Verify: Calculation is accurate

### 5. Edge Case Tests
- [ ] **Self-Referral Prevention**
  - Test: Try to register with own referral code
  - Expected: Should fail validation
  - Verify: User cannot refer themselves

- [ ] **SQL Injection Protection**
  - Test: Submit malicious referral code
  - Expected: Validation error, no SQL injection
  - Verify: Database integrity maintained

- [ ] **Unicode Characters**
  - Test: Submit referral code with unicode characters
  - Expected: Validation error
  - Verify: System handles gracefully

- [ ] **Extremely Long Codes**
  - Test: Submit very long referral code
  - Expected: Validation error
  - Verify: Length limits enforced

### 6. Database Integrity Tests
- [ ] **Foreign Key Constraints**
  - Test: Try to delete affiliate with referred users
  - Expected: Database constraints prevent deletion
  - Verify: Data integrity maintained

- [ ] **Concurrent Payout Requests**
  - Test: Submit multiple payout requests simultaneously
  - Expected: Only one succeeds
  - Verify: Race conditions handled

- [ ] **Data Consistency**
  - Test: Verify all referral-related data is consistent
  - Expected: No orphaned records
  - Verify: Data relationships maintained

### 7. Performance Tests
- [ ] **Large Number of Referrals**
  - Test: Affiliate with 1000+ referred users
  - Expected: Dashboard loads quickly
  - Verify: Performance acceptable

- [ ] **Commission Job Performance**
  - Test: Run job with many referrals
  - Expected: Job completes within reasonable time
  - Verify: No memory issues

### 8. Security Tests
- [ ] **Authorization Checks**
  - Test: Access referral data without authentication
  - Expected: Access denied
  - Verify: Security enforced

- [ ] **Data Privacy**
  - Test: Verify referral data is properly protected
  - Expected: Only authorized access
  - Verify: No data leakage

### 9. Admin Settings Tests
- [ ] **Commission Rate Update**
  - Test: Change referral commission rate in admin
  - Expected: Rate saved and applied correctly
  - Verify: New rate used in calculations

- [ ] **Rate Validation**
  - Test: Try to set rate outside 0.5%-2% range
  - Expected: Validation error
  - Verify: Invalid rates rejected

### 10. Financial Accuracy Tests
- [ ] **Precision Calculations**
  - Test: Verify calculations with decimal precision
  - Expected: Accurate to 4 decimal places
  - Verify: No rounding errors

- [ ] **Audit Trail**
  - Test: Verify all financial changes are logged
  - Expected: Complete audit trail
  - Verify: All transactions traceable

## 🚨 Critical Pass/Fail Criteria

### MUST PASS (100% Required)
- All registration scenarios work correctly
- Payout calculations are 100% accurate
- Commission calculations are mathematically precise
- No data integrity issues
- All security validations work
- Database constraints enforced

### SHOULD PASS (High Priority)
- Performance acceptable under load
- All edge cases handled gracefully
- Admin interface works correctly
- Dashboard displays accurate data

## 📋 Testing Procedure

1. **Setup Test Environment**
   - Create test affiliate accounts
   - Set referral commission rate to 1.5%
   - Create test users with different scenarios

2. **Execute Tests in Order**
   - Start with registration tests
   - Move to commission calculations
   - Test payout system
   - Verify dashboard display
   - Check edge cases

3. **Verify Each Step**
   - Check database records
   - Verify calculations manually
   - Test UI displays
   - Confirm error handling

4. **Document Results**
   - Record pass/fail for each test
   - Note any issues found
   - Verify fixes work correctly

## ⚠️ Production Deployment Checklist

- [ ] All critical tests pass 100%
- [ ] Database migrations run successfully
- [ ] Commission job scheduled correctly
- [ ] Admin settings configured
- [ ] Monitoring in place
- [ ] Backup procedures verified
- [ ] Rollback plan ready

## 📞 Emergency Procedures

If any financial discrepancies are found:
1. **STOP** all payout processing immediately
2. **Investigate** the specific issue
3. **VERIFY** data integrity
4. **CORRECT** any errors manually
5. **TEST** fixes thoroughly
6. **RESUME** operations only after verification

---

**⚠️ IMPORTANT**: This system handles real money. All tests must pass 100% before production deployment. Any financial discrepancies must be investigated and resolved immediately.
