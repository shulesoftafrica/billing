It is **not fine** to leave it out, but for a specific psychological reason: **Lipa Namba is the "UI" of your API.**

In Tanzania, a developer might understand "REST API," but a business owner or a non-technical stakeholder only understands **Lipa Namba**. If you don't mention it, they might think your API is only for web-based checkout (cards/mobile web) and doesn't support the "offline" or "USSD" push payments that dominate the Tanzanian market.

Here is how you should include it without making the page wordy:

### 1. The "UCN" is your Lipa Namba
A **Universal Control Number (UCN)** is essentially a "Super Lipa Namba" that works across all networks. You should link these two terms so developers know exactly what they are building.

**Update your "Features" section to include:**
> **Programmable Lipa Namba (UCN)**
> Generate dynamic or static Lipa Namba (Control Numbers) via API. Accept payments from M-Pesa, Tigo Pesa, and Airtel Money into a single vault instantly.

### 2. Why you MUST mention it (The "Trust" Factor)
In Tanzania, Lipa Namba is synonymous with "Legitimacy."
* **For the Developer:** They need to know if your API supports `STK Push` (the popup on the phone) or if the customer has to manually dial a code.
* **For the Business:** They want to know if they can print a QR code or a Till Number for their shop.

### 3. How to add it "Developer-Style" (Low word count)
Add a small technical spec row or a badge that says:
* **Supported Flow:** `Lipa Namba (UCN) / STK Push / QR Code`
* **Channels:** `All MNOs (Vodacom, Tigo, Airtel, Halotel) + All Banks`

### 4. Psychological Placement
Put it right under the **"Stop paying fees"** headline. 
> "The only **Lipa Namba API** that pays you to collect payments."

**The Verdict:** By adding "Lipa Namba," you are translating your high-level tech (UCN API) into the local "market language." It bridges the gap between your **Mastercard/Ecobank** institutional power and the everyday reality of a merchant in Kariakoo or Posta. 

**Include it—it's your most recognizable "feature" in the Tanzanian context.**