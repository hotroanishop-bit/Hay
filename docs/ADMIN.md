# Huong Dan Quan Tri

## Truy Cap Admin Panel

**URL:** `https://yourdomain.com/admin`

**Yeu cau:** Tai khoan co quyen admin (`is_admin = 1`)

## Dashboard

Trang tong quan voi cac thong ke:

### Stats Cards

- **Total Users**: Tong so nguoi dung
- **Active Today**: Nguoi dung hoat dong hom nay
- **Total Revenue**: Tong doanh thu tu deposits
- **API Calls Today**: So luot goi API hom nay
- **Pending Deposits**: Lenh nap dang cho duyet
- **Pending Tickets**: Tickets chua xu ly

### Charts

- Revenue 7 ngay gan nhat
- So nguoi dang ky 7 ngay gan nhat

### Recent Activity

- Danh sach hoat dong gan day (dang ky, nap tien, tao key)

## Quan Ly Users

**Menu:** Admin > Users

### Danh sach Users

- Tim kiem theo email/name
- Filter: Active, Inactive, Banned, All
- Phan trang

### Thao tac voi User

| Action | Mo ta |
|--------|-------|
| View | Xem chi tiet user |
| Edit | Sua thong tin user |
| Ban | Khoa tai khoan |
| Unban | Mo khoa tai khoan |
| Edit Balance | Thay doi so du |
| Change Plan | Doi goi dich vu |
| Impersonate | Dang nhap nhu user |

### Ban User

1. Click "Ban" tren user
2. Nhap ly do (optional)
3. Xac nhan

**Luu y:** User bi ban se tu dong logout va khong the dang nhap lai.

### Chinh sua Balance

1. Click "Edit Balance"
2. Nhap so tien can them/bot
3. Chon loai: Add (cong) hoac Subtract (tru)
4. Nhap ly do
5. Xac nhan

**Luu y:** Moi thay doi duoc ghi vao Audit Log.

### Impersonate (Dang nhap nhu User)

1. Click "Login as User"
2. He thong chuyen sang giao dien cua user
3. Xem dashboard, API keys, billing nhu user thay
4. Click "Exit Impersonation" de thoat

## Quan Ly Deposits

**Menu:** Admin > Deposits

### Tab Filter

- **All**: Tat ca deposits
- **Pending**: Cho duyet
- **Approved**: Da duyet
- **Rejected**: Tu choi
- **Expired**: Het han

### Thong tin Deposit

| Field | Mo ta |
|-------|-------|
| ID | Ma giao dich |
| User | Nguoi nap |
| Amount | So tien (VND) |
| Reference | Ma tham chieu |
| Status | Trang thai |
| Created | Thoi gian tao |
| QR Code | Ma QR VietQR |

### Duyet Deposit

1. Click vao deposit Pending
2. Xem thong tin va QR code
3. Kiem tra chuyen khoan that:
   - Dung so tien
   - Dung noi dung chuyen khoan (reference code)
4. Click "Approve" neu hop le
5. Hoac "Reject" voi ly do neu khong hop le

**Khi Approve:**
- Tien duoc cong vao balance cua user
- User nhan notification
- Ghi Audit Log

### Reject Deposit

1. Click "Reject"
2. Nhap ly do (bat buoc)
3. Xac nhan

**Luu y:** User se nhan notification voi ly do bi tu choi.

## Quan Ly Tickets

**Menu:** Admin > Tickets

### Tab Filter

- **All**: Tat ca tickets
- **Open**: Moi tao
- **In Progress**: Dang xu ly
- **Resolved**: Da giai quyet
- **Closed**: Da dong

### Priority Badges

- **Low**: Xanh la
- **Medium**: Vang
- **High**: Cam
- **Urgent**: Do

### Xu ly Ticket

1. Click vao ticket
2. Doc noi dung va conversation
3. Tra loi trong form Reply
4. Click "Send Reply"
5. Doi status neu can (In Progress -> Resolved)

### Dong Ticket

1. Click "Close Ticket"
2. Xac nhan
3. Ticket chuyen trang thai Closed

### Internal Notes

- Ghi chu noi bo (user khong thay)
- Dung de trao doi giua cac admin
- Click "Add Internal Note"

## System Settings

**Menu:** Admin > Settings

### General Settings

| Setting | Mo ta |
|---------|-------|
| Site Name | Ten website |
| Site URL | URL chinh |
| Logo URL | URL logo |
| Favicon URL | URL favicon |

### Maintenance Settings

| Setting | Mo ta |
|---------|-------|
| Maintenance Mode | Bat/tat che do bao tri |
| Maintenance Message | Thong bao hien thi |

**Khi bat Maintenance Mode:**
- User thuong khong the truy cap
- Chi admin moi vao duoc
- Hien thi trang bao tri

### Payment Settings (VietQR)

| Setting | Mo ta |
|---------|-------|
| Bank Name | Ten ngan hang |
| Account Number | So tai khoan |
| Account Holder | Chu tai khoan |
| Min Deposit | So tien nap toi thieu |
| Max Deposit | So tien nap toi da |

### Email Settings (SMTP)

| Setting | Mo ta |
|---------|-------|
| SMTP Host | smtp.gmail.com |
| SMTP Port | 587 |
| SMTP Username | Email |
| SMTP Password | App password |
| Encryption | TLS / SSL / None |

**Test Email:**
- Click "Test Connection"
- He thong gui email test
- Kiem tra hop thu

### Limits Settings

| Setting | Mo ta |
|---------|-------|
| Default Plan | Plan mac dinh cho user moi |
| Max API Keys | So key toi da moi user |
| Session Timeout | Thoi gian timeout (phut) |

## Plans Management

**Menu:** Admin > Plans

### Tao Plan Moi

1. Click "Create Plan"
2. Nhap thong tin:
   - Name: Ten plan
   - Price: Gia (VND/thang)
   - Features: Cac tinh nang (JSON)
   - Rate Limit: Gioi han request/phut
   - Daily Limit: Gioi han request/ngay
3. Click "Save"

### Chinh sua Plan

1. Click vao plan
2. Sua thong tin
3. Click "Update"

**Luu y:** Thay doi plan khong anh huong users hien tai cho den ky thanh toan tiep theo.

## Model Pricing

**Menu:** Admin > Model Pricing

### Quan ly gia Model

| Field | Mo ta |
|-------|-------|
| Model ID | ID model (vd: gpt-4) |
| Display Name | Ten hien thi |
| Input Price | Gia input/1K tokens |
| Output Price | Gia output/1K tokens |
| Is Active | Bat/tat model |

### Them Model Moi

1. Click "Add Model"
2. Nhap thong tin gia
3. Click "Save"

### Mapping Model

| Field | Mo ta |
|-------|-------|
| Exposed Model | Model user thay |
| Actual Model | Model that goi API |

Vi du: User goi `gpt-4` -> Backend goi `claude-3-sonnet`

## Campaigns

**Menu:** Admin > Campaigns

### Tao Campaign

1. Click "Create Campaign"
2. Nhap thong tin:
   - Name: Ten campaign
   - Slug: URL path (vd: `tet-2025`)
   - Description: Mo ta
   - Bonus Tokens: So tokens tang
   - Bonus Credits: So credits tang
   - Max Registrations: Gioi han nguoi (0 = unlimited)
   - Start Date: Ngay bat dau
   - End Date: Ngay ket thuc
3. Click "Save"

### URL Campaign

```
https://yourdomain.com/c/your-slug
https://yourdomain.com/register?campaign=your-slug
```

### Thong ke Campaign

- Tong nguoi dang ky qua campaign
- Tong tokens/credits da phat
- Danh sach nguoi dang ky
- Trang thai (Active/Expired/Full)

## Gift Codes

**Menu:** Admin > Gift Codes

### Tao Gift Code

1. Click "Create Code"
2. Nhap:
   - Code: Ma code (hoac de tu dong tao)
   - Type: Tokens hoac Credits
   - Value: Gia tri
   - Max Uses: So lan su dung toi da
   - Expires At: Ngay het han
3. Click "Save"

### Bulk Generate

1. Click "Bulk Generate"
2. Nhap:
   - Prefix: Tien to (vd: TET2025-)
   - Quantity: So luong
   - Type & Value: Nhu tren
3. Click "Generate"
4. Download danh sach codes (CSV)

### Thong ke

- Tong codes da tao
- Codes da dung
- Codes con lai
- Export CSV

## Audit Logs

**Menu:** Admin > Audit Logs

### Thong tin Log

| Field | Mo ta |
|-------|-------|
| Admin | Admin thuc hien |
| Action | Hanh dong |
| Target | Doi tuong bi tac dong |
| Old Value | Gia tri cu |
| New Value | Gia tri moi |
| IP Address | Dia chi IP |
| Timestamp | Thoi gian |

### Filter

- Theo Admin
- Theo Action type
- Theo ngay

### Action Types

- user.ban / user.unban
- user.balance_update
- user.plan_change
- deposit.approve / deposit.reject
- ticket.reply / ticket.close
- setting.update
- plan.create / plan.update
- model.create / model.update

## System Health

**Menu:** Admin > System Health

### Metrics

| Metric | Mo ta |
|--------|-------|
| Database | Trang thai ket noi MySQL |
| Upstream API | Trang thai provider APIs |
| Disk Space | Dung luong o dia |
| Memory Usage | Su dung RAM |
| PHP Version | Phien ban PHP |

### API Upstream Status

- Kiem tra ket noi toi cac provider
- Hien thi latency
- Alert khi co van de

## Best Practices

### 1. Duyet Deposits

- Kiem tra ky luong chuyen khoan that
- Doi chieu reference code
- Duyet trong ngay de user khong phai cho lau

### 2. Xu ly Tickets

- Tra loi nhanh chong (trong 24h)
- Su dung internal notes de trao doi
- Dong ticket khi da giai quyet

### 3. Security

- Doi password dinh ky
- Bat 2FA
- Kiem tra Audit Logs thuong xuyen
- Khong chia se tai khoan admin

### 4. Backup

- Backup database hang ngay
- Backup truoc khi cap nhat lon
- Luu tru backup o vi tri an toan

### 5. Monitoring

- Theo doi System Health
- Set up alerts
- Review logs dinh ky

## Shortcuts

| Shortcut | Action |
|----------|--------|
| Ctrl+K | Quick search |
| G D | Go to Dashboard |
| G U | Go to Users |
| G S | Go to Settings |
| ? | Hien thi shortcuts |

## Lien He Ho Tro

Neu gap van de voi Admin Panel:
- Kiem tra Audit Logs
- Kiem tra error logs
- Lien he developer support
