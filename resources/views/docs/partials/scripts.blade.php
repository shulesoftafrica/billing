<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    
    // Load saved theme or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', savedTheme);
    updateThemeIcon();

    themeToggle?.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon();
    });

    function updateThemeIcon() {
        const theme = html.getAttribute('data-theme');
        if (themeToggle) {
            themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
    }

    // Mobile Sidebar Toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');

    mobileToggle?.addEventListener('click', function() {
        sidebar?.classList.toggle('mobile-open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 980) {
            if (!sidebar?.contains(e.target) && !mobileToggle?.contains(e.target)) {
                sidebar?.classList.remove('mobile-open');
            }
        }
    });

    // Sidebar Navigation Accordion
    document.querySelectorAll('.nav-section-toggle').forEach(toggle => {
        // Skip if it's an anchor link (authentication)
        if (toggle.tagName === 'A') return;
        
        toggle.addEventListener('click', function() {
            const section = this.closest('.nav-section');
            section?.classList.toggle('collapsed');
        });
    });

    // Endpoint Accordion
    document.querySelectorAll('.endpoint-header').forEach(header => {
        header.addEventListener('click', function(e) {
            // Don't toggle if clicking on copy button
            if (e.target.closest('.copy-button')) return;
            
            const card = this.closest('.endpoint-card');
            const body = card?.querySelector('.endpoint-body');
            
            card?.classList.toggle('open');
            
            // Set max-height for smooth animation
            if (card?.classList.contains('open')) {
                if (body) {
                    body.style.maxHeight = body.scrollHeight + 'px';
                }
            } else {
                if (body) {
                    body.style.maxHeight = '0';
                }
            }
        });
    });

    // Code Tabs Switching
    document.querySelectorAll('.code-tabs').forEach(tabsContainer => {
        const tabs = tabsContainer.querySelectorAll('.code-tab');
        const contents = tabsContainer.parentElement.querySelectorAll('.code-tab-content');

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', function() {
                // Remove active from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                // Add active to clicked tab and corresponding content
                tab.classList.add('active');
                if (contents[index]) {
                    contents[index].classList.add('active');
                }
            });
        });
    });

    // Copy to Clipboard
    document.querySelectorAll('.copy-button').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.stopPropagation(); // Prevent accordion toggle
            
            const wrapper = this.closest('.code-block-wrapper');
            const pre = wrapper?.querySelector('pre');
            const code = pre?.textContent || '';

            try {
                await navigator.clipboard.writeText(code);
                
                const originalText = this.textContent;
                this.textContent = '✓ Copied!';
                this.classList.add('copied');

                setTimeout(() => {
                    this.textContent = originalText;
                    this.classList.remove('copied');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy:', err);
                this.textContent = '✗ Failed';
                setTimeout(() => {
                    this.textContent = 'Copy';
                }, 2000);
            }
        });
    });

    // Search Functionality
    const searchInput = document.getElementById('endpointSearch');
    
    searchInput?.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        // Search through navigation sections and links
        document.querySelectorAll('.nav-section').forEach(section => {
            const isAuthSection = section.querySelector('a.nav-section-toggle');
            
            if (isAuthSection) {
                // Handle authentication section
                const matches = 'authentication'.includes(query);
                section.style.display = query === '' || matches ? 'block' : 'none';
            } else {
                // Handle regular sections with links
                const links = section.querySelectorAll('.nav-link');
                let hasVisibleLinks = false;

                links.forEach(link => {
                    const text = link.textContent.toLowerCase();
                    const matches = text.includes(query);
                    link.style.display = query === '' || matches ? 'flex' : 'none';
                    if (matches || query === '') hasVisibleLinks = true;
                });

                // Hide section if no links match
                section.style.display = hasVisibleLinks ? 'block' : 'none';
                
                // Expand section if it has matches
                if (hasVisibleLinks && query !== '') {
                    section.classList.remove('collapsed');
                }
            }
        });

        // Search through endpoint cards in main content
        document.querySelectorAll('.endpoint-card').forEach(card => {
            const title = card.querySelector('.endpoint-name')?.textContent.toLowerCase() || '';
            const url = card.querySelector('.endpoint-url')?.textContent.toLowerCase() || '';
            const method = card.querySelector('.method-badge')?.textContent.toLowerCase() || '';
            const description = card.querySelector('.endpoint-body')?.textContent.toLowerCase() || '';
            
            const matches = title.includes(query) || 
                          url.includes(query) || 
                          method.includes(query) ||
                          description.includes(query);
            
            card.style.display = query === '' || matches ? 'block' : 'none';
        });

        // Search through sections
        document.querySelectorAll('.api-section').forEach(section => {
            const cards = section.querySelectorAll('.endpoint-card');
            const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
            
            // Hide section if no visible cards
            section.style.display = visibleCards.length > 0 || query === '' ? 'block' : 'none';
        });
    });

    // Smooth scroll to anchor with offset
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                const offset = 80; // Account for fixed header
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile sidebar
                if (window.innerWidth <= 980) {
                    sidebar?.classList.remove('mobile-open');
                }
            }
        });
    });

    // Highlight active section in sidebar based on scroll
    const observerOptions = {
        root: null,
        rootMargin: '-80px 0px -80% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                
                // Remove active from all links
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });

                // Add active to matching link
                const activeLink = document.querySelector(`.nav-link[href="#${id}"]`);
                activeLink?.classList.add('active');
            }
        });
    }, observerOptions);

    // Observe all sections and endpoint cards with IDs
    document.querySelectorAll('.api-section[id], .endpoint-card[id]').forEach(section => {
        observer.observe(section);
    });

    // Initialize: Expand all sections by default
    document.querySelectorAll('.nav-section').forEach(section => {
        section.classList.remove('collapsed');
    });

    // Open first endpoint in each section by default (optional)
    // Uncomment if you want first endpoint open by default
    /*
    document.querySelectorAll('.api-section').forEach(section => {
        const firstCard = section.querySelector('.endpoint-card');
        if (firstCard) {
            firstCard.classList.add('open');
            const body = firstCard.querySelector('.endpoint-body');
            if (body) {
                body.style.maxHeight = body.scrollHeight + 'px';
            }
        }
    });
    */

    console.log('✅ Documentation initialized');
});
</script>
