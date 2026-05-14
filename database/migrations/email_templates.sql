-- Email Templates Migration
-- Admin-editable email templates for notifications

CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables JSON DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO email_templates (name, subject, body, variables, is_active) VALUES
(
    'welcome',
    'Welcome to Hay API Gateway, {{user_name}}!',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #6366f1;">Welcome to Hay API Gateway!</h1>
    <p>Hi {{user_name}},</p>
    <p>Thank you for joining Hay API Gateway. We''re excited to have you on board!</p>
    <p>Here''s what you can do next:</p>
    <ul>
        <li>Create your first API key</li>
        <li>Explore our documentation</li>
        <li>Start making API calls</li>
    </ul>
    <p>If you have any questions, our support team is here to help.</p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "user_email"]',
    1
),
(
    'deposit_approved',
    'Deposit Approved - ${{amount}} Added',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #22c55e;">Deposit Approved!</h1>
    <p>Hi {{user_name}},</p>
    <p>Great news! Your deposit has been approved and credited to your account.</p>
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Amount:</strong> ${{amount}}</p>
        <p style="margin: 10px 0 0;"><strong>Reference:</strong> {{reference_code}}</p>
        <p style="margin: 10px 0 0;"><strong>New Balance:</strong> ${{new_balance}}</p>
    </div>
    <p>You can now use your credits to access our AI models.</p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "amount", "reference_code", "new_balance"]',
    1
),
(
    'deposit_rejected',
    'Deposit Request Rejected',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #ef4444;">Deposit Rejected</h1>
    <p>Hi {{user_name}},</p>
    <p>We regret to inform you that your deposit request has been rejected.</p>
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Amount:</strong> ${{amount}}</p>
        <p style="margin: 10px 0 0;"><strong>Reference:</strong> {{reference_code}}</p>
        <p style="margin: 10px 0 0;"><strong>Reason:</strong> {{reason}}</p>
    </div>
    <p>If you believe this is an error, please contact our support team.</p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "amount", "reference_code", "reason"]',
    1
),
(
    'low_balance',
    'Low Balance Alert - Action Required',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #f59e0b;">Low Balance Alert</h1>
    <p>Hi {{user_name}},</p>
    <p>Your account balance is running low. To avoid service interruptions, please add more credits soon.</p>
    <div style="background: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Current Balance:</strong> ${{current_balance}}</p>
        <p style="margin: 10px 0 0;"><strong>Threshold:</strong> ${{threshold}}</p>
    </div>
    <p><a href="{{deposit_url}}" style="display: inline-block; background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Add Credits Now</a></p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "current_balance", "threshold", "deposit_url"]',
    1
),
(
    'plan_expiring',
    'Your Plan is Expiring Soon',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #6366f1;">Plan Expiring Soon</h1>
    <p>Hi {{user_name}},</p>
    <p>Your {{plan_name}} plan will expire on {{expiry_date}}.</p>
    <p>To continue enjoying our services without interruption, please renew your plan.</p>
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Current Plan:</strong> {{plan_name}}</p>
        <p style="margin: 10px 0 0;"><strong>Expiry Date:</strong> {{expiry_date}}</p>
        <p style="margin: 10px 0 0;"><strong>Days Remaining:</strong> {{days_remaining}}</p>
    </div>
    <p><a href="{{renewal_url}}" style="display: inline-block; background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Renew Now</a></p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "plan_name", "expiry_date", "days_remaining", "renewal_url"]',
    1
),
(
    'password_reset',
    'Reset Your Password',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #6366f1;">Password Reset Request</h1>
    <p>Hi {{user_name}},</p>
    <p>We received a request to reset your password. Click the button below to set a new password:</p>
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{reset_url}}" style="display: inline-block; background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Reset Password</a>
    </p>
    <p>This link will expire in {{expiry_hours}} hours.</p>
    <p>If you did not request this password reset, please ignore this email or contact support if you have concerns.</p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "reset_url", "expiry_hours"]',
    1
),
(
    'email_verification',
    'Verify Your Email Address',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #6366f1;">Verify Your Email</h1>
    <p>Hi {{user_name}},</p>
    <p>Please verify your email address by clicking the button below:</p>
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{verify_url}}" style="display: inline-block; background: #6366f1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px;">Verify Email</a>
    </p>
    <p>This link will expire in {{expiry_hours}} hours.</p>
    <p>If you did not create an account, please ignore this email.</p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
    '["user_name", "verify_url", "expiry_hours"]',
    1
);
