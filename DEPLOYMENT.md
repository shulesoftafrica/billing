# Production Deployment Guide

## The Issue
Your production server is missing the compiled Vite assets (`public/build/manifest.json`) because these files are excluded from git via `.gitignore`.

## ✅ RECOMMENDED: Build Assets on Production Server

### Step 1: SSH into your production server
```bash
ssh your-user@your-server
cd /usr/share/nginx/html/billing
```

### Step 2: Install Node.js (if not already installed)
```bash
# Check if Node.js is installed
node -v
npm -v

# If not installed (Ubuntu/Debian):
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Or (CentOS/RHEL):
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo yum install -y nodejs
```

### Step 3: Build the assets
```bash
# Install dependencies
npm install

# Build for production
npm run build

# Verify the build
ls -la public/build/
```

### Step 4: Set proper permissions
```bash
sudo chown -R www-data:www-data public/build
sudo chmod -R 755 public/build
```

### Step 5: Clear Laravel cache
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

---

## Alternative: Use Deployment Script

I've created `deploy.sh` that automates this process:

```bash
# Make it executable
chmod +x deploy.sh

# Run deployment (requires sudo for some operations)
./deploy.sh
```

---

## Alternative: Commit Built Assets (Not Recommended)

If you can't build on the server, you can commit the built assets:

### On your local machine:
```powershell
# The .gitignore has been updated to allow public/build
npm run build
git add public/build .gitignore
git commit -m "Include built Vite assets for production"
git push origin master
```

### On production server:
```bash
cd /usr/share/nginx/html/billing
git pull origin master
sudo chown -R www-data:www-data public/build
sudo chmod -R 755 public/build
php artisan config:clear
php artisan view:clear
```

**⚠️ Note:** This increases repository size and can cause merge conflicts.

---

## CI/CD Pipeline (Best Long-term Solution)

Set up GitHub Actions to build assets automatically:

Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy to Production

on:
  push:
    branches: [ master ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'
          
      - name: Build assets
        run: |
          npm ci
          npm run build
          
      - name: Deploy to server
        uses: easingthemes/ssh-deploy@main
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          REMOTE_HOST: ${{ secrets.REMOTE_HOST }}
          REMOTE_USER: ${{ secrets.REMOTE_USER }}
          TARGET: /usr/share/nginx/html/billing
```

---

## Verifying the Fix

After deployment, check:

```bash
# Verify manifest exists
cat /usr/share/nginx/html/billing/public/build/manifest.json

# Check permissions
ls -la /usr/share/nginx/html/billing/public/build/

# Test the application
curl http://your-domain.com
```

---

## Troubleshooting

### If build fails with memory errors:
```bash
# Increase Node.js memory limit
export NODE_OPTIONS="--max-old-space-size=4096"
npm run build
```

### If permissions denied:
```bash
sudo chown -R $USER:www-data /usr/share/nginx/html/billing
sudo chmod -R 755 /usr/share/nginx/html/billing
```

### If still getting errors:
```bash
# Check Laravel logs
tail -f /usr/share/nginx/html/billing/storage/logs/laravel.log

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log
```
