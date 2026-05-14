<?php
/**
 * Email Template Model
 * Handles email template data operations
 */

class EmailTemplate extends BaseModel
{
    protected string $table = 'email_templates';
    
    protected array $fillable = [
        'name',
        'subject',
        'body',
        'variables',
        'is_active',
        'created_at',
        'updated_at'
    ];

    /**
     * Default templates for reset functionality
     */
    private array $defaultTemplates = [
        'welcome' => [
            'subject' => 'Welcome to Hay API Gateway, {{user_name}}!',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h1 style="color: #6366f1;">Welcome to Hay API Gateway!</h1>
    <p>Hi {{user_name}},</p>
    <p>Thank you for joining Hay API Gateway. We\'re excited to have you on board!</p>
    <p>Here\'s what you can do next:</p>
    <ul>
        <li>Create your first API key</li>
        <li>Explore our documentation</li>
        <li>Start making API calls</li>
    </ul>
    <p>If you have any questions, our support team is here to help.</p>
    <p>Best regards,<br>The Hay Team</p>
</div>',
            'variables' => '["user_name", "user_email"]'
        ],
        'deposit_approved' => [
            'subject' => 'Deposit Approved - ${{amount}} Added',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
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
            'variables' => '["user_name", "amount", "reference_code", "new_balance"]'
        ],
        'deposit_rejected' => [
            'subject' => 'Deposit Request Rejected',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
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
            'variables' => '["user_name", "amount", "reference_code", "reason"]'
        ],
        'low_balance' => [
            'subject' => 'Low Balance Alert - Action Required',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
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
            'variables' => '["user_name", "current_balance", "threshold", "deposit_url"]'
        ],
        'plan_expiring' => [
            'subject' => 'Your Plan is Expiring Soon',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
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
            'variables' => '["user_name", "plan_name", "expiry_date", "days_remaining", "renewal_url"]'
        ],
        'password_reset' => [
            'subject' => 'Reset Your Password',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
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
            'variables' => '["user_name", "reset_url", "expiry_hours"]'
        ],
        'email_verification' => [
            'subject' => 'Verify Your Email Address',
            'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
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
            'variables' => '["user_name", "verify_url", "expiry_hours"]'
        ]
    ];

    /**
     * Get template by name
     */
    public function getByName(string $name): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name AND is_active = 1 LIMIT 1";
        $result = $this->query($sql, ['name' => $name]);
        return $result[0] ?? null;
    }

    /**
     * Get all templates
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        return $this->query($sql, []);
    }

    /**
     * Update template
     */
    public function updateTemplate(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Reset template to default
     */
    public function resetToDefault(int $id): bool
    {
        $template = $this->find($id);
        if (!$template) {
            return false;
        }

        $name = $template['name'];
        if (!isset($this->defaultTemplates[$name])) {
            return false;
        }

        $default = $this->defaultTemplates[$name];
        return $this->update($id, [
            'subject' => $default['subject'],
            'body' => $default['body'],
            'variables' => $default['variables'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get default template content
     */
    public function getDefault(string $name): ?array
    {
        return $this->defaultTemplates[$name] ?? null;
    }

    /**
     * Get template statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                FROM {$this->table}";
        
        $result = $this->query($sql, []);
        return $result[0] ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0
        ];
    }

    /**
     * Toggle template active status
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    /**
     * Render template with variables
     */
    public function render(string $templateName, array $variables): ?array
    {
        $template = $this->getByName($templateName);
        
        // Fallback to default if not in database
        if (!$template) {
            $default = $this->getDefault($templateName);
            if (!$default) {
                return null;
            }
            $template = [
                'subject' => $default['subject'],
                'body' => $default['body']
            ];
        }

        // Replace placeholders
        $subject = $template['subject'];
        $body = $template['body'];

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }
}
