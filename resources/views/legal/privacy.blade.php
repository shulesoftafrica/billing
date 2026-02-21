@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">Billing System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('docs') }}">Documentation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content -->
<div class="container py-5">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <h1 class="display-5 fw-bold mb-2">Privacy Policy</h1>
            <p class="text-muted mb-5">Last Updated: February 18, 2026</p>

            <!-- Introduction -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">1. Introduction</h2>
                <p>
                    This Privacy Policy describes how Billing System ("we", "us", "our") collects, uses, discloses, and protects 
                    personal data in compliance with the Tanzania Data Protection Act, 2022, the Bank of Tanzania (BOT) Payment 
                    Systems Regulations, and international best practices for Payment Service Providers (PSPs).
                </p>
                <p>
                    By using our payment processing platform, you acknowledge that you have read, understood, and agree to be 
                    bound by this Privacy Policy.
                </p>
            </section>

            <!-- Data Controller -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">2. Data Controller Information</h2>
                <div class="card">
                    <div class="card-body">
                        <p class="mb-2"><strong>Entity Name:</strong> Billing System Limited</p>
                        <p class="mb-2"><strong>Registration Number:</strong> [Registration Number]</p>
                        <p class="mb-2"><strong>BOT PSP License Number:</strong> [License Number]</p>
                        <p class="mb-2"><strong>Registered Office:</strong> [Physical Address], Dar es Salaam, Tanzania</p>
                        <p class="mb-2"><strong>Data Protection Officer:</strong> dpo@billing.com</p>
                        <p class="mb-0"><strong>Contact:</strong> +255 XXX XXX XXX</p>
                    </div>
                </div>
            </section>

            <!-- Legal Basis -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">3. Legal Basis for Processing</h2>
                <p>We process personal data under the following legal grounds as per the Tanzania Data Protection Act, 2022:</p>
                <ul>
                    <li><strong>Contractual Necessity:</strong> Processing required to perform our payment services contract</li>
                    <li><strong>Legal Obligation:</strong> Compliance with BOT regulations, anti-money laundering (AML) laws, and Know Your Customer (KYC) requirements</li>
                    <li><strong>Legitimate Interest:</strong> Fraud prevention, risk management, and service improvement</li>
                    <li><strong>Consent:</strong> Marketing communications and optional service features</li>
                </ul>
            </section>

            <!-- Information We Collect -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">4. Information We Collect</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">4.1 Personal Identification Data</h3>
                <ul>
                    <li>Full name and date of birth</li>
                    <li>Email address and phone number</li>
                    <li>Physical and postal address</li>
                    <li>National Identification Number (NIDA) or Passport details</li>
                    <li>Tax Identification Number (TIN)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">4.2 Financial Information</h3>
                <ul>
                    <li>Bank account details and IBAN</li>
                    <li>Mobile money wallet numbers</li>
                    <li>Payment card information (processed via PCI DSS compliant processors)</li>
                    <li>Transaction history and payment patterns</li>
                    <li>Credit assessment data (where applicable)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">4.3 Business Information (For Merchants)</h3>
                <ul>
                    <li>Business registration certificate</li>
                    <li>TIN and VAT registration</li>
                    <li>Beneficial ownership information</li>
                    <li>Business bank account details</li>
                    <li>Trading history and volume</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">4.4 Technical Data</h3>
                <ul>
                    <li>IP address and device identifiers</li>
                    <li>Browser type and operating system</li>
                    <li>API access logs and timestamps</li>
                    <li>Geographical location data</li>
                    <li>Cookie and tracking data</li>
                </ul>
            </section>

            <!-- How We Use Data -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">5. How We Use Your Data</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">5.1 Payment Processing</h3>
                <ul>
                    <li>Executing payment transactions and subscriptions</li>
                    <li>Settlement and reconciliation services</li>
                    <li>Issuing invoices and payment confirmations</li>
                    <li>Managing refunds and disputes</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">5.2 Regulatory Compliance</h3>
                <ul>
                    <li>KYC and customer due diligence (CDD) procedures</li>
                    <li>Anti-Money Laundering (AML) and Counter-Terrorism Financing (CTF) checks</li>
                    <li>Reporting to BOT and Financial Intelligence Unit (FIU)</li>
                    <li>Tax compliance and reporting to Tanzania Revenue Authority (TRA)</li>
                    <li>Sanctions screening and politically exposed persons (PEP) checks</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">5.3 Risk Management</h3>
                <ul>
                    <li>Fraud detection and prevention</li>
                    <li>Transaction monitoring and anomaly detection</li>
                    <li>Credit risk assessment</li>
                    <li>Security incident response</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">5.4 Service Delivery</h3>
                <ul>
                    <li>Account management and customer support</li>
                    <li>API access and integration support</li>
                    <li>Service improvement and product development</li>
                    <li>Communication about service updates</li>
                </ul>
            </section>

            <!-- Data Sharing -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">6. Data Sharing and Disclosure</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">6.1 Mandatory Disclosures</h3>
                <p>We are required to share data with:</p>
                <ul>
                    <li><strong>Bank of Tanzania (BOT):</strong> Regulatory reporting and oversight</li>
                    <li><strong>Financial Intelligence Unit (FIU):</strong> Suspicious transaction reports</li>
                    <li><strong>Tanzania Revenue Authority (TRA):</strong> Tax compliance</li>
                    <li><strong>Law Enforcement:</strong> When legally compelled by court order</li>
                    <li><strong>Data Protection Commissioner:</strong> As required by law</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">6.2 Service Providers</h3>
                <p>We share data with trusted third parties who assist in:</p>
                <ul>
                    <li>Payment gateway and card processing (PCI DSS Level 1 certified)</li>
                    <li>Mobile money integration partners (licensed by TCRA)</li>
                    <li>Banking partners for settlement</li>
                    <li>Cloud infrastructure and data hosting (within Tanzania or approved jurisdictions)</li>
                    <li>Identity verification and KYC services</li>
                    <li>Fraud prevention and security services</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">6.3 Business Transfers</h3>
                <p>
                    In the event of merger, acquisition, or sale of assets, your data may be transferred to the successor entity, 
                    subject to BOT approval and notification to affected data subjects.
                </p>
            </section>

            <!-- Data Security -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">7. Data Security Measures</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">7.1 Technical Safeguards</h3>
                <ul>
                    <li><strong>Encryption:</strong> AES-256 encryption at rest, TLS 1.3 in transit</li>
                    <li><strong>Access Controls:</strong> Role-based access control (RBAC) and multi-factor authentication</li>
                    <li><strong>Network Security:</strong> Firewall protection, intrusion detection systems (IDS)</li>
                    <li><strong>Tokenization:</strong> Sensitive financial data tokenized and never stored in plain text</li>
                    <li><strong>Regular Audits:</strong> Annual penetration testing and security assessments</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">7.2 Organizational Measures</h3>
                <ul>
                    <li>Data protection and security awareness training</li>
                    <li>Incident response and breach notification procedures</li>
                    <li>Data minimization and retention policies</li>
                    <li>Vendor risk management and due diligence</li>
                    <li>Business continuity and disaster recovery plans</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">7.3 PCI DSS Compliance</h3>
                <p>
                    Our payment processing infrastructure is PCI DSS Level 1 compliant, ensuring the highest standard of 
                    payment card data security.
                </p>
            </section>

            <!-- Data Retention -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">8. Data Retention</h2>
                <p>We retain personal data in accordance with legal and regulatory requirements:</p>
                <ul>
                    <li><strong>Transaction Records:</strong> 7 years (BOT Payment Systems Regulations)</li>
                    <li><strong>KYC/CDD Documentation:</strong> 7 years after relationship ends (AML requirements)</li>
                    <li><strong>Financial Statements:</strong> 10 years (Companies Act)</li>
                    <li><strong>Tax Records:</strong> 10 years (TRA requirements)</li>
                    <li><strong>Marketing Consent:</strong> Until withdrawn or 3 years of inactivity</li>
                    <li><strong>Technical Logs:</strong> 90 days (unless required for investigations)</li>
                </ul>
                <p>
                    After retention periods expire, data is securely deleted or anonymized in accordance with our data 
                    disposal policy.
                </p>
            </section>

            <!-- Cross-Border Transfers -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">9. Cross-Border Data Transfers</h2>
                <p>
                    Personal data may be transferred outside Tanzania only to jurisdictions recognized by the Data Protection 
                    Commissioner as providing adequate protection, or where:
                </p>
                <ul>
                    <li>You have provided explicit consent</li>
                    <li>The transfer is necessary for contract performance</li>
                    <li>Standard contractual clauses (SCCs) approved by the Commissioner are in place</li>
                    <li>The recipient is certified under an approved framework</li>
                </ul>
                <p>
                    All cross-border transfers are documented and reported to BOT as required by payment systems regulations.
                </p>
            </section>

            <!-- Your Rights -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">10. Your Data Protection Rights</h2>
                <p>Under the Tanzania Data Protection Act, 2022, you have the following rights:</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-info-circle text-primary me-2"></i>Right to Access</h5>
                                <p class="card-text small">Request copies of your personal data</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-pencil text-primary me-2"></i>Right to Rectification</h5>
                                <p class="card-text small">Correct inaccurate or incomplete data</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-trash text-primary me-2"></i>Right to Erasure</h5>
                                <p class="card-text small">Request deletion (subject to legal obligations)</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-hand-stop text-primary me-2"></i>Right to Restrict</h5>
                                <p class="card-text small">Limit how we use your data</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-arrow-right-circle text-primary me-2"></i>Right to Portability</h5>
                                <p class="card-text small">Receive your data in machine-readable format</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-x-circle text-primary me-2"></i>Right to Object</h5>
                                <p class="card-text small">Object to processing for specific purposes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mt-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Some rights may be limited where we have legal or regulatory obligations to 
                    retain data (e.g., AML records, tax documents, BOT reporting requirements).
                </div>

                <p class="mt-3">To exercise your rights, contact our Data Protection Officer at: <a href="mailto:dpo@billing.com">dpo@billing.com</a></p>
            </section>

            <!-- Data Breach -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">11. Data Breach Notification</h2>
                <p>
                    In the event of a personal data breach, we will:
                </p>
                <ul>
                    <li>Notify the Data Protection Commissioner within 72 hours of discovery</li>
                    <li>Notify the Bank of Tanzania immediately for payment-related breaches</li>
                    <li>Inform affected data subjects without undue delay if there is high risk to their rights</li>
                    <li>Document all breaches and remedial actions taken</li>
                    <li>Implement measures to prevent future occurrences</li>
                </ul>
            </section>

            <!-- Cookies -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">12. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar technologies for:</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Authentication, security, and session management</li>
                    <li><strong>Performance Cookies:</strong> Analytics and service improvement</li>
                    <li><strong>Functional Cookies:</strong> Language preferences and user settings</li>
                    <li><strong>Targeting Cookies:</strong> Marketing (only with your consent)</li>
                </ul>
                <p>You can manage cookie preferences through your browser settings.</p>
            </section>

            <!-- Children's Privacy -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">13. Children's Privacy</h2>
                <p>
                    Our services are not intended for persons under 18 years of age. We do not knowingly collect data from minors. 
                    If we discover that we have collected data from a minor, we will delete it promptly and notify the guardian.
                </p>
            </section>

            <!-- Automated Decision Making -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">14. Automated Decision Making</h2>
                <p>
                    We may use automated systems for:
                </p>
                <ul>
                    <li>Fraud detection and transaction risk scoring</li>
                    <li>Credit assessment (where applicable)</li>
                    <li>Account verification and KYC checks</li>
                </ul>
                <p>
                    You have the right to request human review of automated decisions that significantly affect you. 
                    Contact our Data Protection Officer to exercise this right.
                </p>
            </section>

            <!-- Updates -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">15. Policy Updates</h2>
                <p>
                    We may update this Privacy Policy to reflect changes in our practices, technology, legal requirements, 
                    or other factors. We will notify you of material changes through:
                </p>
                <ul>
                    <li>Email notification (30 days advance notice)</li>
                    <li>In-app notifications</li>
                    <li>Posting on our website</li>
                </ul>
                <p>Continued use of our services after changes constitutes acceptance of the updated policy.</p>
            </section>

            <!-- Complaints -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">16. Complaints and Dispute Resolution</h2>
                <p>If you have concerns about our data handling practices:</p>
                <ol>
                    <li>Contact our Data Protection Officer: <a href="mailto:dpo@billing.com">dpo@billing.com</a></li>
                    <li>We will investigate and respond within 30 days</li>
                    <li>If unsatisfied, you may lodge a complaint with:
                        <ul class="mt-2">
                            <li><strong>Tanzania Data Protection Commissioner</strong><br>
                                Website: <a href="https://www.tanzania.go.tz" target="_blank">www.tanzania.go.tz</a><br>
                                Email: info@dataprotection.go.tz
                            </li>
                            <li><strong>Bank of Tanzania (for payment-related issues)</strong><br>
                                Website: <a href="https://www.bot.go.tz" target="_blank">www.bot.go.tz</a><br>
                                Email: info@bot.go.tz
                            </li>
                        </ul>
                    </li>
                </ol>
            </section>

            <!-- Contact -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">17. Contact Information</h2>
                <div class="card">
                    <div class="card-body">
                        <p class="mb-2"><strong>Data Protection Officer</strong></p>
                        <p class="mb-2">Email: dpo@billing.com</p>
                        <p class="mb-2">Phone: +255 XXX XXX XXX</p>
                        <p class="mb-2">Address: [Physical Address], Dar es Salaam, Tanzania</p>
                        <p class="mb-0">Office Hours: Monday - Friday, 8:00 AM - 5:00 PM EAT</p>
                    </div>
                </div>
            </section>

            <!-- Acknowledgment -->
            <section class="mb-5">
                <div class="alert alert-info">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Compliance Statement:</strong> This Privacy Policy complies with the Tanzania Data Protection Act, 
                    2022, the Bank of Tanzania Payment Systems Regulations, Anti-Money Laundering Act, and international best 
                    practices including PCI DSS and ISO 27001 standards.
                </div>
            </section>

            <hr class="my-5">

            <div class="text-center">
                <p class="text-muted mb-3">Related Documents</p>
                <a href="{{ route('terms') }}" class="btn btn-outline-primary me-2">Terms of Service</a>
                <a href="{{ route('docs') }}" class="btn btn-outline-secondary">API Documentation</a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-2">&copy; {{ date('Y') }} Billing System. All rights reserved.</p>
        <p class="small text-white-50">Licensed Payment Service Provider - Bank of Tanzania</p>
    </div>
</footer>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endpush
@endsection
