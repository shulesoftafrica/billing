@extends('layouts.app')

@section('title', 'Terms of Service')

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
            <h1 class="display-5 fw-bold mb-2">Terms of Service</h1>
            <p class="text-muted mb-5">Last Updated: February 18, 2026</p>

            <!-- Introduction -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">1. Agreement to Terms</h2>
                <p>
                    These Terms of Service ("Terms") constitute a legally binding agreement between you ("User", "Merchant", 
                    "Customer", "you") and Billing System Limited ("Billing System", "we", "us", "our"), a licensed Payment 
                    Service Provider registered in the United Republic of Tanzania.
                </p>
                <p>
                    By accessing or using our payment processing platform, API services, or related services (collectively, 
                    the "Services"), you agree to be bound by these Terms, our Privacy Policy, and all applicable laws and 
                    regulations of Tanzania.
                </p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> If you do not agree to these Terms, you must not use our Services.
                </div>
            </section>

            <!-- Definitions -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">2. Definitions</h2>
                <dl class="row">
                    <dt class="col-sm-3">Account</dt>
                    <dd class="col-sm-9">Your registered account on the Billing System platform</dd>
                    
                    <dt class="col-sm-3">BOT</dt>
                    <dd class="col-sm-9">Bank of Tanzania, the central bank and payment systems regulator</dd>
                    
                    <dt class="col-sm-3">Customer</dt>
                    <dd class="col-sm-9">End-user who receives goods or services from a Merchant and makes payments through our platform</dd>
                    
                    <dt class="col-sm-3">Merchant</dt>
                    <dd class="col-sm-9">Business or individual that uses our Services to accept payments</dd>
                    
                    <dt class="col-sm-3">PSP</dt>
                    <dd class="col-sm-9">Payment Service Provider licensed by BOT to provide payment services</dd>
                    
                    <dt class="col-sm-3">Settlement</dt>
                    <dd class="col-sm-9">Transfer of funds from Customer to Merchant through our platform</dd>
                    
                    <dt class="col-sm-3">Transaction</dt>
                    <dd class="col-sm-9">Any payment, refund, or fund transfer processed through our Services</dd>
                </dl>
            </section>

            <!-- Eligibility -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">3. Eligibility</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">3.1 General Requirements</h3>
                <p>To use our Services, you must:</p>
                <ul>
                    <li>Be at least 18 years of age</li>
                    <li>Have legal capacity to enter into binding contracts</li>
                    <li>Be a resident of Tanzania or an entity registered in Tanzania</li>
                    <li>Provide accurate, complete, and current information</li>
                    <li>Comply with all applicable laws and regulations</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">3.2 Merchant Requirements</h3>
                <p>Merchants must additionally:</p>
                <ul>
                    <li>Hold valid business registration in Tanzania</li>
                    <li>Possess Tax Identification Number (TIN) and VAT registration (if applicable)</li>
                    <li>Maintain a business bank account or mobile money merchant account</li>
                    <li>Operate in compliance with BOT regulations and industry standards</li>
                    <li>Not be engaged in prohibited or restricted business activities (see Section 9)</li>
                </ul>
            </section>

            <!-- Account Registration -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">4. Account Registration and KYC</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">4.1 Registration Process</h3>
                <p>
                    To create an Account, you must complete our registration process and provide all required information, 
                    including but not limited to:
                </p>
                <ul>
                    <li>Full legal name and date of birth</li>
                    <li>National ID (NIDA), Passport, or business registration certificate</li>
                    <li>Physical address and contact information</li>
                    <li>Tax Identification Number (TIN)</li>
                    <li>Bank account or mobile money wallet details</li>
                    <li>Beneficial ownership information (for entities)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">4.2 KYC and Verification</h3>
                <p>
                    In compliance with the Anti-Money Laundering Act and BOT regulations, we conduct Know Your Customer (KYC) 
                    and Customer Due Diligence (CDD) procedures. You agree to:
                </p>
                <ul>
                    <li>Provide truthful, accurate, and complete information</li>
                    <li>Submit supporting documentation for verification</li>
                    <li>Update information promptly when changes occur</li>
                    <li>Undergo enhanced due diligence if required</li>
                    <li>Cooperate with periodic re-verification</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">4.3 Account Approval</h3>
                <p>
                    We reserve the right to approve or reject any Account application at our sole discretion. Approval may be 
                    delayed pending additional verification. We may also impose transaction limits or other restrictions based 
                    on risk assessment.
                </p>
            </section>

            <!-- Services Provided -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">5. Payment Services Provided</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">5.1 Core Services</h3>
                <p>As a licensed PSP, we provide the following services:</p>
                <ul>
                    <li><strong>Payment Processing:</strong> Acceptance and processing of card payments, mobile money, and bank transfers</li>
                    <li><strong>Subscription Management:</strong> Recurring billing and subscription handling</li>
                    <li><strong>Invoice Generation:</strong> Automated invoice creation and distribution</li>
                    <li><strong>Settlement Services:</strong> Transfer of funds to Merchant accounts</li>
                    <li><strong>Refunds and Chargebacks:</strong> Processing customer refunds and dispute resolution</li>
                    <li><strong>API Access:</strong> Technical integration for automated payment workflows</li>
                    <li><strong>Reporting and Analytics:</strong> Transaction data and financial reporting</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">5.2 Payment Methods</h3>
                <p>We support the following payment methods:</p>
                <ul>
                    <li>International payment cards (Visa, Mastercard, American Express)</li>
                    <li>Mobile money wallets (M-Pesa, Tigo Pesa, Airtel Money, Halopesa)</li>
                    <li>Bank transfers and direct debits</li>
                    <li>SWIFT international transfers</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">5.3 Service Availability</h3>
                <p>
                    We strive to provide 99.9% uptime but do not guarantee uninterrupted service. Services may be temporarily 
                    unavailable due to:
                </p>
                <ul>
                    <li>Scheduled maintenance (notified in advance)</li>
                    <li>Emergency security updates</li>
                    <li>Third-party service disruptions</li>
                    <li>Force majeure events</li>
                </ul>
            </section>

            <!-- Transaction Terms -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">6. Transaction Processing and Settlement</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">6.1 Transaction Authorization</h3>
                <p>
                    By submitting a transaction through our platform, you authorize us to:
                </p>
                <ul>
                    <li>Process the payment through relevant payment networks</li>
                    <li>Deduct applicable fees and charges</li>
                    <li>Hold funds in accordance with settlement terms</li>
                    <li>Reverse or refund transactions as necessary</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">6.2 Settlement Schedule</h3>
                <p>Settlement timelines vary by payment method and risk profile:</p>
                <ul>
                    <li><strong>Standard Settlement:</strong> T+2 business days (default)</li>
                    <li><strong>Mobile Money:</strong> T+1 business day</li>
                    <li><strong>Card Payments:</strong> T+2 to T+5 business days (subject to card network rules)</li>
                    <li><strong>International Payments:</strong> T+5 to T+10 business days</li>
                </ul>
                <p>
                    We reserve the right to delay settlement if we suspect fraud, breach of Terms, or for regulatory compliance.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">6.3 Reserve Account (Rolling Reserve)</h3>
                <p>
                    For high-risk Merchants or certain business categories, we may require a rolling reserve of 5-20% of 
                    transaction value to be held for 30-180 days as protection against chargebacks, refunds, and disputes.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">6.4 Currency and Conversion</h3>
                <p>
                    All transactions are processed in Tanzanian Shillings (TZS) unless otherwise specified. For foreign currency 
                    transactions, we apply the prevailing interbank exchange rate plus a conversion fee of 1-3%.
                </p>
            </section>

            <!-- Fees and Charges -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">7. Fees, Charges, and Pricing</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">7.1 Transaction Fees</h3>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Payment Method</th>
                                <th>Transaction Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Local Cards</td>
                                <td>2.5% + TZS 500</td>
                            </tr>
                            <tr>
                                <td>International Cards</td>
                                <td>3.5% + TZS 800</td>
                            </tr>
                            <tr>
                                <td>Mobile Money</td>
                                <td>2.0% (min TZS 500, max TZS 5,000)</td>
                            </tr>
                            <tr>
                                <td>Bank Transfer</td>
                                <td>1.5% + TZS 1,000</td>
                            </tr>
                            <tr>
                                <td>SWIFT Transfer</td>
                                <td>$25 + correspondent bank fees</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="h5 fw-bold mt-4 mb-3">7.2 Additional Fees</h3>
                <ul>
                    <li><strong>Chargeback Fee:</strong> TZS 10,000 per chargeback (in addition to transaction reversal)</li>
                    <li><strong>Refund Processing:</strong> TZS 500 per refund</li>
                    <li><strong>Payout Fee:</strong> TZS 1,000 per manual payout</li>
                    <li><strong>Account Maintenance:</strong> Free (no monthly/annual fees)</li>
                    <li><strong>API Access:</strong> Free for standard usage (rate limits apply)</li>
                    <li><strong>Dispute Management:</strong> TZS 5,000 per disputed transaction</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">7.3 Fee Changes</h3>
                <p>
                    We may modify fees with 60 days advance notice. Continued use of Services after fee changes constitutes 
                    acceptance of new pricing.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">7.4 Taxes</h3>
                <p>
                    All fees are exclusive of applicable taxes. You are responsible for:
                </p>
                <ul>
                    <li>VAT on transaction fees (18%)</li>
                    <li>Withholding tax on service fees (where applicable)</li>
                    <li>Income tax on revenue received through the platform</li>
                </ul>
            </section>

            <!-- Refunds and Chargebacks -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">8. Refunds, Chargebacks, and Disputes</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">8.1 Merchant Refund Policy</h3>
                <p>
                    Merchants are responsible for establishing their own refund policies. However, you must process legitimate 
                    refund requests within 14 days. We reserve the right to force refunds for fraudulent or unauthorized 
                    transactions.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">8.2 Chargebacks</h3>
                <p>
                    A chargeback occurs when a Customer disputes a transaction with their bank or card issuer. If a chargeback is 
                    filed:
                </p>
                <ul>
                    <li>The transaction amount is immediately debited from your Account</li>
                    <li>A chargeback fee of TZS 10,000 is assessed</li>
                    <li>You have 7 days to submit compelling evidence to contest</li>
                    <li>We will represent your case to the card network</li>
                    <li>Final decisions are made by the card issuer (not reversible by us)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">8.3 Excessive Chargebacks</h3>
                <p>
                    If your chargeback ratio exceeds 1% of transaction volume or you receive more than 10 chargebacks in a month, 
                    we may:
                </p>
                <ul>
                    <li>Increase your rolling reserve requirement</li>
                    <li>Delay settlement periods</li>
                    <li>Impose additional monitoring or restrictions</li>
                    <li>Suspend or terminate your Account</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">8.4 Dispute Resolution</h3>
                <p>
                    For disputes between you and Customers, you agree to:
                </p>
                <ul>
                    <li>Attempt good faith resolution directly with the Customer</li>
                    <li>Provide accurate information and supporting documentation</li>
                    <li>Comply with our dispute resolution procedures</li>
                    <li>Cooperate with investigations</li>
                </ul>
            </section>

            <!-- Prohibited Activities -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">9. Prohibited and Restricted Activities</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">9.1 Prohibited Businesses</h3>
                <p>You may not use our Services for:</p>
                <ul>
                    <li>Illegal goods or services</li>
                    <li>Drugs, narcotics, or controlled substances</li>
                    <li>Weapons, explosives, or ammunition</li>
                    <li>Counterfeit or stolen goods</li>
                    <li>Pyramid schemes or multi-level marketing</li>
                    <li>Adult content or escort services</li>
                    <li>Online gambling or betting (without proper license)</li>
                    <li>Money laundering or terrorist financing</li>
                    <li>Sanctions evasion or sanctioned entities</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">9.2 Prohibited Activities</h3>
                <p>You must not:</p>
                <ul>
                    <li>Process transactions for third parties without authorization</li>
                    <li>Engage in credit repair or debt collection services</li>
                    <li>Split single transactions to avoid fees or limits</li>
                    <li>Process your own credit card payments (self-dealing)</li>
                    <li>Engage in fraudulent or deceptive practices</li>
                    <li>Violate card network rules or payment scheme regulations</li>
                    <li>Use the platform to test stolen credit card numbers</li>
                    <li>Circumvent or manipulate our fee structure</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">9.3 High-Risk Activities</h3>
                <p>The following activities are permitted but subject to enhanced due diligence and higher reserves:</p>
                <ul>
                    <li>Travel and tourism services</li>
                    <li>Cryptocurrencies and digital assets</li>
                    <li>Subscription-based services</li>
                    <li>Crowdfunding or donation platforms</li>
                    <li>High-value luxury goods</li>
                </ul>
            </section>

            <!-- Compliance -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">10. Regulatory Compliance and Obligations</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">10.1 BOT Compliance</h3>
                <p>
                    As a licensed PSP under BOT oversight, we comply with the Bank of Tanzania Act, Payment Systems Act, and 
                    all BOT directives. You agree to cooperate with BOT inspections and provide information as required.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">10.2 AML/CFT Compliance</h3>
                <p>
                    In accordance with the Anti-Money Laundering Act and Financial Intelligence Act, we:
                </p>
                <ul>
                    <li>Conduct ongoing transaction monitoring</li>
                    <li>Screen against sanctions lists and PEP databases</li>
                    <li>Report suspicious transactions to the Financial Intelligence Unit (FIU)</li>
                    <li>Maintain records for 7 years</li>
                    <li>Implement risk-based controls</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">10.3 Tax Compliance</h3>
                <p>
                    You are responsible for:
                </p>
                <ul>
                    <li>Declaring all income received through the platform</li>
                    <li>Paying applicable income tax and VAT</li>
                    <li>Maintaining proper accounting records</li>
                    <li>Filing tax returns with Tanzania Revenue Authority (TRA)</li>
                </ul>
                <p>
                    We may report transaction data to TRA as required by law.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">10.4 Data Protection</h3>
                <p>
                    Both parties must comply with the Tanzania Data Protection Act, 2022. See our 
                    <a href="{{ route('privacy') }}">Privacy Policy</a> for details on data handling.
                </p>
            </section>

            <!-- Fund Safeguarding -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">11. Fund Safeguarding and Security</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">11.1 Safeguarding of Client Funds</h3>
                <p>
                    In compliance with BOT regulations, customer funds are:
                </p>
                <ul>
                    <li>Held in segregated trust accounts at licensed commercial banks in Tanzania</li>
                    <li>Kept separate from our operational funds</li>
                    <li>Protected in the event of our insolvency</li>
                    <li>Subject to regular reconciliation and audits</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">11.2 Account Security</h3>
                <p>
                    You are responsible for:
                </p>
                <ul>
                    <li>Maintaining confidentiality of login credentials and API keys</li>
                    <li>Enabling two-factor authentication</li>
                    <li>Monitoring your Account for unauthorized activity</li>
                    <li>Immediately reporting suspected security breaches</li>
                    <li>Implementing security best practices for API integration</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">11.3 Fraud Prevention</h3>
                <p>
                    We employ advanced fraud detection systems. You agree to:
                </p>
                <ul>
                    <li>Cooperate with fraud investigations</li>
                    <li>Provide requested documentation promptly</li>
                    <li>Accept temporary holds on suspicious transactions</li>
                    <li>Implement your own fraud prevention measures</li>
                </ul>
            </section>

            <!-- Liability -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">12. Limitation of Liability</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">12.1 Service Limitations</h3>
                <p>
                    Our Services are provided "AS IS" without warranties of any kind. We do not guarantee:
                </p>
                <ul>
                    <li>Uninterrupted or error-free service</li>
                    <li>Specific transaction success rates</li>
                    <li>Compatibility with all payment methods or banks</li>
                    <li>Protection against all fraud or unauthorized transactions</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">12.2 Liability Cap</h3>
                <p>
                    To the maximum extent permitted by law, our total liability to you for any claim arising from or related to 
                    these Terms shall not exceed the lesser of:
                </p>
                <ul>
                    <li>The total fees paid by you in the 3 months preceding the claim, or</li>
                    <li>TZS 5,000,000 (Five Million Tanzanian Shillings)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">12.3 Excluded Damages</h3>
                <p>
                    We shall not be liable for:
                </p>
                <ul>
                    <li>Indirect, incidental, or consequential damages</li>
                    <li>Loss of profits, revenue, or business opportunities</li>
                    <li>Loss of data or corruption of data</li>
                    <li>Reputational harm or business interruption</li>
                    <li>Third-party actions or decisions (including banks, card networks, mobile money operators)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">12.4 Your Indemnification</h3>
                <p>
                    You agree to indemnify and hold us harmless from any claims, damages, losses, or expenses arising from:
                </p>
                <ul>
                    <li>Your breach of these Terms</li>
                    <li>Your violation of laws or regulations</li>
                    <li>Your negligence or misconduct</li>
                    <li>Disputes with your Customers</li>
                    <li>Chargebacks or refunds related to your transactions</li>
                    <li>Intellectual property infringement claims</li>
                </ul>
            </section>

            <!-- Suspension and Termination -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">13. Suspension and Termination</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">13.1 Suspension</h3>
                <p>
                    We may immediately suspend your Account without notice if:
                </p>
                <ul>
                    <li>We suspect fraud, breach of Terms, or illegal activity</li>
                    <li>Your chargeback ratio exceeds acceptable limits</li>
                    <li>You fail KYC verification or provide false information</li>
                    <li>Required by law, BOT directive, or card network rules</li>
                    <li>We identify significant security risks</li>
                    <li>Your Account balance becomes negative</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">13.2 Termination by Us</h3>
                <p>
                    We may terminate your Account with 30 days notice, or immediately for cause. Upon termination:
                </p>
                <ul>
                    <li>You must cease using our Services</li>
                    <li>All pending transactions will be processed or refunded</li>
                    <li>Settlement will occur after reserve period expires</li>
                    <li>You remain liable for chargebacks and refunds for 180 days</li>
                    <li>API access and credentials will be revoked</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">13.3 Termination by You</h3>
                <p>
                    You may terminate your Account at any time by providing 30 days written notice. You must:
                </p>
                <ul>
                    <li>Cease processing new transactions</li>
                    <li>Fulfill all pending orders and subscriptions</li>
                    <li>Settle all outstanding obligations</li>
                    <li>Maintain reserve funds for 180 days (chargeback exposure period)</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">13.4 Effect of Termination</h3>
                <p>
                    Termination does not affect:
                </p>
                <ul>
                    <li>Rights and obligations accrued before termination</li>
                    <li>Provisions that by nature should survive (liability, indemnification, dispute resolution)</li>
                    <li>Our right to withhold funds for chargebacks and refunds</li>
                </ul>
            </section>

            <!-- Intellectual Property -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">14. Intellectual Property Rights</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">14.1 Our Intellectual Property</h3>
                <p>
                    All platform code, APIs, documentation, trademarks, and logos are our exclusive property. You are granted 
                    a limited, non-exclusive, non-transferable license to use our Services for their intended purpose only.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">14.2 Your Content and Data</h3>
                <p>
                    You retain ownership of your data but grant us a license to use, process, and store your data as necessary 
                    to provide Services. You represent that you have all necessary rights to the content you submit.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">14.3 Brand Usage</h3>
                <p>
                    You may display our payment badges and logos on your website. We may reference you as a customer in our 
                    marketing materials unless you opt out in writing.
                </p>
            </section>

            <!-- Dispute Resolution -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">15. Dispute Resolution and Governing Law</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">15.1 Governing Law</h3>
                <p>
                    These Terms are governed by the laws of the United Republic of Tanzania. In case of conflict between these 
                    Terms and applicable law, the law shall prevail.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">15.2 Dispute Resolution Process</h3>
                <p>In the event of a dispute:</p>
                <ol>
                    <li><strong>Negotiation (30 days):</strong> Parties shall attempt good faith negotiation</li>
                    <li><strong>Mediation (60 days):</strong> If unresolved, submit to mediation in Dar es Salaam</li>
                    <li><strong>Arbitration:</strong> Binding arbitration under Tanzania Arbitration Act, 2020</li>
                </ol>

                <h3 class="h5 fw-bold mt-4 mb-3">15.3 Arbitration</h3>
                <p>Arbitration shall be conducted:</p>
                <ul>
                    <li>In Dar es Salaam, Tanzania</li>
                    <li>In English language</li>
                    <li>Before a single arbitrator agreed upon by both parties</li>
                    <li>Under the rules of the Tanzania Institute of Arbitrators</li>
                    <li>With each party bearing their own costs</li>
                </ul>

                <h3 class="h5 fw-bold mt-4 mb-3">15.4 Jurisdiction</h3>
                <p>
                    For matters not subject to arbitration, the courts of Dar es Salaam, Tanzania shall have exclusive jurisdiction.
                </p>
            </section>

            <!-- Force Majeure -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">16. Force Majeure</h2>
                <p>
                    We shall not be liable for failure to perform due to circumstances beyond our reasonable control, including:
                </p>
                <ul>
                    <li>Natural disasters, epidemics, pandemics</li>
                    <li>Government actions, regulations, or directives</li>
                    <li>War, terrorism, civil unrest</li>
                    <li>Internet or telecommunications failures</li>
                    <li>Banking system failures or liquidity crises</li>
                    <li>Card network or payment infrastructure outages</li>
                </ul>
                <p>
                    In such events, our performance obligations are suspended for the duration of the force majeure event.
                </p>
            </section>

            <!-- General Provisions -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">17. General Provisions</h2>
                
                <h3 class="h5 fw-bold mt-4 mb-3">17.1 Amendment</h3>
                <p>
                    We may modify these Terms at any time by providing 30 days notice. Material changes will be communicated 
                    via email. Continued use after changes constitutes acceptance.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">17.2 Assignment</h3>
                <p>
                    You may not assign or transfer your rights under these Terms without our written consent. We may assign 
                    our rights to any affiliate or successor entity with BOT approval.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">17.3 Severability</h3>
                <p>
                    If any provision is found invalid or unenforceable, the remaining provisions shall remain in full effect.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">17.4 Waiver</h3>
                <p>
                    No waiver of any provision shall be deemed a further or continuing waiver. Our failure to enforce any term 
                    does not constitute a waiver of that term.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">17.5 Entire Agreement</h3>
                <p>
                    These Terms, together with our Privacy Policy and any supplemental agreements, constitute the entire agreement 
                    between you and us.
                </p>

                <h3 class="h5 fw-bold mt-4 mb-3">17.6 Notices</h3>
                <p>
                    All notices must be in writing and sent to:
                </p>
                <ul>
                    <li><strong>For Us:</strong> legal@billing.com or our registered office address</li>
                    <li><strong>For You:</strong> Email address or physical address on file</li>
                </ul>
            </section>

            <!-- Contact -->
            <section class="mb-5">
                <h2 class="h4 fw-bold mb-3">18. Contact Information</h2>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Billing System Limited</h5>
                        <p class="mb-2"><strong>Registered Office:</strong> [Physical Address], Dar es Salaam, Tanzania</p>
                        <p class="mb-2"><strong>Business Registration:</strong> [Registration Number]</p>
                        <p class="mb-2"><strong>BOT License Number:</strong> [PSP License Number]</p>
                        <p class="mb-2"><strong>TIN:</strong> [Tax ID Number]</p>
                        <p class="mb-2"><strong>Email:</strong> support@billing.com</p>
                        <p class="mb-2"><strong>Legal Inquiries:</strong> legal@billing.com</p>
                        <p class="mb-2"><strong>Phone:</strong> +255 XXX XXX XXX</p>
                        <p class="mb-0"><strong>Support Hours:</strong> Monday - Friday, 8:00 AM - 8:00 PM EAT</p>
                    </div>
                </div>
            </section>

            <!-- Acknowledgment -->
            <section class="mb-5">
                <div class="alert alert-info">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Regulatory Compliance:</strong> These Terms of Service comply with the Bank of Tanzania Payment 
                    Systems Regulations, Anti-Money Laundering Act, Tanzania Data Protection Act 2022, Electronic and Postal 
                    Communications Act, and international payment card network rules (Visa, Mastercard, PCI DSS).
                </div>
            </section>

            <hr class="my-5">

            <div class="text-center">
                <p class="text-muted mb-3">Related Documents</p>
                <a href="{{ route('privacy') }}" class="btn btn-outline-primary me-2">Privacy Policy</a>
                <a href="{{ route('docs') }}" class="btn btn-outline-secondary">API Documentation</a>
            </div>

            <hr class="my-4">

            <div class="alert alert-success text-center">
                <p class="mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    By clicking "I Accept" during registration, you acknowledge that you have read, understood, and agree to 
                    be bound by these Terms of Service.
                </p>
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
