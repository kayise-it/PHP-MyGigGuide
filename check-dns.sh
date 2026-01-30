#!/bin/bash
# DNS Records Verification Script

echo "=================================================================================="
echo "Checking DNS Records for mygigguide.co.za"
echo "=================================================================================="
echo ""

echo "1. MX Record:"
MX=$(dig +short mx mygigguide.co.za)
if [ -z "$MX" ]; then
    echo "   ❌ MX record not found"
else
    echo "   ✅ MX record found:"
    echo "   $MX"
fi
echo ""

echo "2. A Record for mail subdomain:"
A=$(dig +short mail.mygigguide.co.za)
if [ -z "$A" ]; then
    echo "   ❌ A record for mail.mygigguide.co.za not found"
else
    echo "   ✅ A record found: $A"
fi
echo ""

echo "3. SPF Record:"
SPF=$(dig +short txt mygigguide.co.za | grep -i spf)
if [ -z "$SPF" ]; then
    echo "   ❌ SPF record not found"
else
    echo "   ✅ SPF record found:"
    echo "   $SPF"
fi
echo ""

echo "4. DKIM Record:"
DKIM=$(dig +short txt default._domainkey.mygigguide.co.za)
if [ -z "$DKIM" ]; then
    echo "   ❌ DKIM record not found"
else
    echo "   ✅ DKIM record found:"
    echo "   $DKIM"
fi
echo ""

echo "5. DMARC Record:"
DMARC=$(dig +short txt _dmarc.mygigguide.co.za)
if [ -z "$DMARC" ]; then
    echo "   ❌ DMARC record not found"
else
    echo "   ✅ DMARC record found:"
    echo "   $DMARC"
fi
echo ""

echo "=================================================================================="
if [ -n "$MX" ] && [ -n "$A" ] && [ -n "$SPF" ] && [ -n "$DKIM" ] && [ -n "$DMARC" ]; then
    echo "✅ All DNS records are configured!"
    echo "You can now test sending emails."
else
    echo "⚠️  Some DNS records are missing. Please add them at your domain registrar."
    echo "See: /var/www/mygigguide/REQUIRED_DNS_RECORDS.txt"
fi
echo "=================================================================================="
