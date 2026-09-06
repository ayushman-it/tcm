# 🔄 System Flow Diagram

## 📊 Complete Referral & Wallet Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                     REFERRAL SYSTEM FLOW                            │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────┐
│  STUDENT A   │  (Referrer)
│  (Referrer)  │
└──────┬───────┘
       │
       │ 1. Goes to Profile
       │
       ▼
┌─────────────────────┐
│  Profile Page       │
│  ┌───────────────┐  │
│  │ Referral ID:  │  │
│  │  TCM123456    │◄─┼─── Unique code from database (users.referral_id)
│  └───────────────┘  │
└──────┬──────────────┘
       │
       │ 2. Shares code with friend
       │
       ▼
┌──────────────┐
│  STUDENT B   │  (New Student)
│ (New Student)│
└──────┬───────┘
       │
       │ 3. Makes payment
       │
       ▼
┌─────────────────────────────────────┐
│  Payment Submission Form            │
│  ┌───────────────────────────────┐  │
│  │ Amount: ₹999                  │  │
│  │ Date: 2026-06-19              │  │
│  │ Method: UPI                   │  │
│  │ Transaction ID: T12345        │  │
│  │ Referral Code: TCM123456  ◄───┼──── NEW FIELD!
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │
       │ 4. Submit button clicked
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│  PaymentController::store()                             │
│  ┌───────────────────────────────────────────────────┐  │
│  │ 1. Extract referral_code = "TCM123456"           │  │
│  │ 2. Query database:                                │  │
│  │    SELECT id FROM users                           │  │
│  │    WHERE referral_id = 'TCM123456'                │  │
│  │ 3. Find referrer_id = 5 (Student A's ID)         │  │
│  │ 4. Insert payment_submissions:                    │  │
│  │    - user_id = 10 (Student B)                     │  │
│  │    - referral_code = "TCM123456"                  │  │
│  │    - referrer_id = 5 (Student A)                  │  │
│  │    - status = 'pending'                           │  │
│  └───────────────────────────────────────────────────┘  │
└──────┬──────────────────────────────────────────────────┘
       │
       │ 5. Payment saved with referral info
       │
       ▼
┌─────────────────────────────────────┐
│  Database: payment_submissions      │
│  ┌───────────────────────────────┐  │
│  │ id: 123                       │  │
│  │ user_id: 10                   │  │
│  │ amount: 999.00                │  │
│  │ referral_code: "TCM123456"    │◄─┼─── Stored for later
│  │ referrer_id: 5                │◄─┼─── Links to Student A
│  │ status: 'pending'             │  │
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │
       │ 6. Admin reviews
       │
       ▼
┌──────────────┐
│    ADMIN     │
└──────┬───────┘
       │
       │ 7. Approves payment
       │
       ▼
┌─────────────────────────────────────────────────────────┐
│  Admin\PaymentController::approve()                     │
│  ┌───────────────────────────────────────────────────┐  │
│  │ 1. Update status = 'approved'                     │  │
│  │ 2. Create enrollment for Student B                │  │
│  │ 3. Call PaymentSubmission::approve(123, $adminId) │  │
│  └─────────────────┬─────────────────────────────────┘  │
└────────────────────┼────────────────────────────────────┘
                     │
                     │ 8. Triggers wallet credit
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  PaymentSubmission::approve()                           │
│  ┌───────────────────────────────────────────────────┐  │
│  │ // Create enrollment, send notifications, etc.    │  │
│  │                                                    │  │
│  │ // 🎁 Credit referral wallet                      │  │
│  │ Wallet::creditReferral(10, 123);  ◄─────────────┐ │  │
│  └──────────────────┬────────────────────────────────┘  │
└─────────────────────┼────────────────────────────────────┘
                      │
                      │ 9. Automatic credit process
                      │
                      ▼
┌─────────────────────────────────────────────────────────┐
│  Wallet::creditReferral()                               │
│  ┌───────────────────────────────────────────────────┐  │
│  │ 1. Get payment: id=123                            │  │
│  │ 2. Extract referral_code = "TCM123456"            │  │
│  │ 3. Find referrer:                                 │  │
│  │    SELECT id FROM users                           │  │
│  │    WHERE referral_id = 'TCM123456'                │  │
│  │ 4. Validate:                                      │  │
│  │    ✓ Referrer exists                              │  │
│  │    ✓ Not self-referral (5 ≠ 10)                  │  │
│  │    ✓ Not already credited                         │  │
│  │ 5. Call Wallet::credit()                          │  │
│  └──────────────────┬────────────────────────────────┘  │
└─────────────────────┼────────────────────────────────────┘
                      │
                      │ 10. Credit wallet
                      │
                      ▼
┌─────────────────────────────────────────────────────────┐
│  Wallet::credit()                                       │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Parameters:                                        │  │
│  │ - userId: 5 (Student A)                           │  │
│  │ - amount: 100                                     │  │
│  │ - description: "Referral bonus — Student B..."   │  │
│  │ - refId: "referral-payment-123"                   │  │
│  │                                                    │  │
│  │ Actions:                                           │  │
│  │ 1. Update wallets:                                │  │
│  │    balance = balance + 100                        │  │
│  │ 2. Insert wallet_transactions:                    │  │
│  │    - type: 'credit'                               │  │
│  │    - amount: 100                                  │  │
│  │    - balance_after: 100                           │  │
│  │ 3. Send notification                              │  │
│  │ 4. Send push notification                         │  │
│  └───────────────────────────────────────────────────┘  │
└──────┬──────────────────────────────────────────────────┘
       │
       │ 11. Notifications sent
       │
       ▼
┌──────────────────────────────────────────┐
│  Student A receives:                     │
│  ┌────────────────────────────────────┐  │
│  │ 🔔 In-App Notification:            │  │
│  │ "💰 ₹100 added to your wallet!"    │  │
│  │                                    │  │
│  │ 📱 Push Notification:              │  │
│  │ "Wallet Credit: ₹100"              │  │
│  │ "Referral bonus — Student B        │  │
│  │  joined via your code!"            │  │
│  └────────────────────────────────────┘  │
└──────┬───────────────────────────────────┘
       │
       │ 12. Student checks wallet
       │
       ▼
┌──────────────┐
│  STUDENT A   │
│  (Referrer)  │
└──────┬───────┘
       │
       │ 13. Opens /student/wallet
       │
       ▼
┌─────────────────────────────────────────────────────┐
│  Wallet Dashboard                                   │
│  ┌───────────────────────────────────────────────┐  │
│  │  💰 Available Balance                         │  │
│  │      ₹100.00                                  │◄─┼─── Success! 🎉
│  │  [Request Withdrawal]                         │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  📊 Total Earned: ₹100.00                           │
│  💸 Total Withdrawn: ₹0.00                          │
│                                                     │
│  📝 Transaction History:                            │
│  ┌───────────────────────────────────────────────┐  │
│  │ + ₹100.00 | Referral bonus — Student B...    │  │
│  │             19 Jun 2026, 4:30 PM              │  │
│  └───────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
       │
       │ 14. Can withdraw when balance ≥ ₹300
       │
       ▼
    [END]

```

---

## 🔄 Withdrawal Flow

```
┌──────────────┐
│  STUDENT     │
└──────┬───────┘
       │
       │ 1. Balance: ₹500
       │
       ▼
┌─────────────────────────────────────┐
│  Wallet Page                        │
│  [Request Withdrawal] ◄─── Click    │
└──────┬──────────────────────────────┘
       │
       │ 2. Modal opens
       │
       ▼
┌─────────────────────────────────────┐
│  Withdrawal Modal                   │
│  ┌───────────────────────────────┐  │
│  │ Amount: [400]                 │  │
│  │ UPI ID: [student@paytm]       │  │
│  │ [Submit Request]              │  │
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │
       │ 3. POST /student/wallet/withdraw
       │
       ▼
┌─────────────────────────────────────┐
│  WalletController::requestWithdrawal│
│  ┌───────────────────────────────┐  │
│  │ Validate:                     │  │
│  │ ✓ Amount ≥ ₹300               │  │
│  │ ✓ Amount ≤ balance            │  │
│  │ ✓ No pending request          │  │
│  │ ✓ Valid UPI ID                │  │
│  │                               │  │
│  │ Insert withdrawal_requests    │  │
│  │ Notify admins                 │  │
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │
       │ 4. Request saved
       │
       ▼
┌─────────────────────────────────────┐
│  Database: withdrawal_requests      │
│  ┌───────────────────────────────┐  │
│  │ user_id: 5                    │  │
│  │ amount: 400                   │  │
│  │ upi_id: "student@paytm"       │  │
│  │ status: 'pending'             │  │
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │
       │ 5. Admin reviews
       │
       ▼
┌──────────────┐
│    ADMIN     │
└──────┬───────┘
       │
       │ 6. Approves at /admin/wallet
       │
       ▼
┌─────────────────────────────────────┐
│  Admin\WalletController::approve()  │
│  ┌───────────────────────────────┐  │
│  │ 1. Update status='approved'   │  │
│  │ 2. Call Wallet::debit()       │  │
│  │ 3. Process payment to UPI     │  │
│  │ 4. Send notification          │  │
│  └───────────────────────────────┘  │
└──────┬──────────────────────────────┘
       │
       │ 7. Money sent
       │
       ▼
┌──────────────┐
│  STUDENT     │  ✅ Receives ₹400 in UPI
└──────────────┘  💰 Balance: ₹100
```

---

## 🤖 TCM Agent Flow

```
┌──────────────┐
│  STUDENT     │
└──────┬───────┘
       │
       │ 1. Opens /student/agent
       │
       ▼
┌─────────────────────────────────────────┐
│  TCM Agent Page                         │
│  ┌─────────────────────────────────┐    │
│  │  🤖 TCM Agent                   │    │
│  │  Your AI learning assistant     │    │
│  │                                 │    │
│  │  Quick suggestions:             │    │
│  │  [📚 Enroll in courses]         │    │
│  │  [💰 Check wallet]              │    │
│  │  [🗓 Upcoming events]           │    │
│  │                                 │    │
│  │  [Type message...]  [Send]      │    │
│  └─────────────────────────────────┘    │
└──────┬──────────────────────────────────┘
       │
       │ 2. Student types: "How do I check my wallet?"
       │
       ▼
┌─────────────────────────────────────────┐
│  Frontend JavaScript                    │
│  ┌─────────────────────────────────┐    │
│  │ POST /student/agent/chat        │    │
│  │ Body: { message: "How do..." }  │    │
│  └─────────────────────────────────┘    │
└──────┬──────────────────────────────────┘
       │
       │ 3. AJAX request
       │
       ▼
┌─────────────────────────────────────────┐
│  AgentController::chat()                │
│  ┌─────────────────────────────────┐    │
│  │ 1. Get message from request     │    │
│  │ 2. Call generateResponse()      │    │
│  │ 3. Return JSON                  │    │
│  └─────────────────────────────────┘    │
└──────┬──────────────────────────────────┘
       │
       │ 4. Response generated
       │
       ▼
┌─────────────────────────────────────────┐
│  Response:                              │
│  {                                      │
│    "success": true,                     │
│    "data": {                            │
│      "message": "Check your wallet...", │
│      "timestamp": "2026-06-19T16:30:00" │
│    }                                    │
│  }                                      │
└──────┬──────────────────────────────────┘
       │
       │ 5. Frontend displays
       │
       ▼
┌─────────────────────────────────────────┐
│  Chat UI                                │
│  ┌─────────────────────────────────┐    │
│  │ 👤 Student: How do I check...   │    │
│  │                                 │    │
│  │ 🤖 Agent: Check your wallet     │    │
│  │    balance and withdrawal       │    │
│  │    history in the 'Wallet'      │    │
│  │    section. You can request...  │    │
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

---

## 📊 Database Schema

```sql
┌─────────────────────────────────────────────┐
│  payment_submissions                        │
├─────────────────────────────────────────────┤
│  id                BIGINT (PK)              │
│  user_id           BIGINT (FK → users)      │
│  item_type         ENUM                     │
│  item_id           BIGINT                   │
│  amount            DECIMAL(10,2)            │
│  payment_method    ENUM                     │
│  transaction_ref   VARCHAR(120)             │
│  referral_code     VARCHAR(50)      ← NEW!  │
│  referrer_id       BIGINT (FK)      ← NEW!  │
│  status            ENUM                     │
│  created_at        DATETIME                 │
└─────────────────────────────────────────────┘
         │                       │
         │                       │ FK
         │                       ▼
         │              ┌──────────────────┐
         │              │  users           │
         │              │  - id            │
         │              │  - referral_id   │
         │              └──────────────────┘
         │
         │ On Approve
         ▼
┌─────────────────────────────────────────────┐
│  wallets                                    │
├─────────────────────────────────────────────┤
│  id                BIGINT (PK)              │
│  user_id           BIGINT (FK → users)      │
│  balance           DECIMAL(10,2)            │
└─────────────────────────────────────────────┘
         │
         │ Transactions
         ▼
┌─────────────────────────────────────────────┐
│  wallet_transactions                        │
├─────────────────────────────────────────────┤
│  id                BIGINT (PK)              │
│  wallet_id         BIGINT (FK)              │
│  user_id           BIGINT (FK)              │
│  type              ENUM (credit/debit)      │
│  amount            DECIMAL(10,2)            │
│  description       VARCHAR(255)             │
│  ref_id            VARCHAR(100)             │
│  balance_after     DECIMAL(10,2)            │
└─────────────────────────────────────────────┘
         │
         │ Withdrawals
         ▼
┌─────────────────────────────────────────────┐
│  withdrawal_requests                        │
├─────────────────────────────────────────────┤
│  id                BIGINT (PK)              │
│  user_id           BIGINT (FK)              │
│  amount            DECIMAL(10,2)            │
│  upi_id            VARCHAR(150)             │
│  status            ENUM                     │
│  processed_at      DATETIME                 │
└─────────────────────────────────────────────┘
```

---

## 🎯 Key Integration Points

1. **Payment Form** → saves referral_code & referrer_id
2. **Admin Approval** → triggers Wallet::creditReferral()
3. **Wallet Model** → validates and credits automatically
4. **Notifications** → sent on wallet credit
5. **Transaction Log** → maintains complete audit trail

**All components work together seamlessly!** ✨
