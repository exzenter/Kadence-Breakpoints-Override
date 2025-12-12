/**
 * Kadence Breakpoints Override - JavaScript Solution
 * 
 * This script replaces hardcoded breakpoint values in all <style> tags
 * after the page has fully loaded.
 * 
 * HOW TO USE:
 * 1. Adjust the breakpoint values below
 * 2. Add this script to your site via:
 *    - A custom plugin
 *    - Theme's functions.php with wp_enqueue_script
 *    - A code snippets plugin
 *    - Directly in Customizer → Additional JavaScript (if available)
 */

(function() {
    'use strict';

    // ===========================================
    // CONFIGURATION - Change these values!
    // ===========================================
    
    const CONFIG = {
        // Original Kadence breakpoints (don't change these)
        original: {
            tablet: 1024,
            mobile: 767
        },
        // Your custom breakpoints (change these!)
        custom: {
            tablet: 900,   // <- Set your tablet breakpoint here
            mobile: 600    // <- Set your mobile breakpoint here
        }
    };

    // ===========================================
    // BREAKPOINT REPLACEMENT LOGIC
    // ===========================================

    function replaceBreakpoints() {
        const styleElements = document.querySelectorAll('style');
        let replacedCount = 0;

        styleElements.forEach(function(styleEl) {
            const originalCSS = styleEl.textContent;
            let modifiedCSS = originalCSS;

            // Replace tablet breakpoint (1024px)
            // Matches: max-width: 1024px, max-width:1024px, min-width: 1025px
            modifiedCSS = modifiedCSS.replace(
                /max-width:\s*1024px/gi,
                'max-width: ' + CONFIG.custom.tablet + 'px'
            );
            modifiedCSS = modifiedCSS.replace(
                /min-width:\s*1025px/gi,
                'min-width: ' + (CONFIG.custom.tablet + 1) + 'px'
            );

            // Replace mobile breakpoint (767px)
            modifiedCSS = modifiedCSS.replace(
                /max-width:\s*767px/gi,
                'max-width: ' + CONFIG.custom.mobile + 'px'
            );
            modifiedCSS = modifiedCSS.replace(
                /min-width:\s*768px/gi,
                'min-width: ' + (CONFIG.custom.mobile + 1) + 'px'
            );

            // Only update if changes were made
            if (modifiedCSS !== originalCSS) {
                styleEl.textContent = modifiedCSS;
                replacedCount++;
            }
        });

        // Also handle <link> stylesheets by injecting override styles
        injectOverrideStyles();

        console.log('[Kadence Breakpoints Override] Modified ' + replacedCount + ' style elements');
        console.log('[Kadence Breakpoints Override] Tablet: ' + CONFIG.original.tablet + 'px → ' + CONFIG.custom.tablet + 'px');
        console.log('[Kadence Breakpoints Override] Mobile: ' + CONFIG.original.mobile + 'px → ' + CONFIG.custom.mobile + 'px');
    }

    function injectOverrideStyles() {
        // Create override styles for external stylesheets
        // This handles CSS loaded via <link> tags that we can't modify directly
        const overrideCSS = `
            /* Kadence Breakpoints Override - Injected Styles */
            
            /* Desktop only (above tablet) */
            @media (min-width: ${CONFIG.custom.tablet + 1}px) {
                .kb-hide-desktop, .kt-hide-desktop { display: none !important; }
            }
            
            /* Tablet range */
            @media (max-width: ${CONFIG.custom.tablet}px) and (min-width: ${CONFIG.custom.mobile + 1}px) {
                .kb-hide-tablet, .kt-hide-tablet { display: none !important; }
            }
            
            /* Mobile only */
            @media (max-width: ${CONFIG.custom.mobile}px) {
                .kb-hide-mobile, .kt-hide-mobile { display: none !important; }
            }
        `;

        const styleEl = document.createElement('style');
        styleEl.id = 'kadence-breakpoints-override-js';
        styleEl.textContent = overrideCSS;
        document.head.appendChild(styleEl);
    }

    // ===========================================
    // INITIALIZATION
    // ===========================================

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', replaceBreakpoints);
    } else {
        // DOM already loaded
        replaceBreakpoints();
    }

    // Also run after full page load (catches late-loaded styles)
    window.addEventListener('load', function() {
        setTimeout(replaceBreakpoints, 100);
    });

    // Watch for dynamically added styles (e.g., from lazy-loaded content)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeName === 'STYLE') {
                    setTimeout(replaceBreakpoints, 10);
                }
            });
        });
    });

    observer.observe(document.head, { childList: true });

})();

