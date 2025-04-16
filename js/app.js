// Main app JavaScript file for Event Dashboard
// Enhanced with Framer Motion animations and modern UI interactions

document.addEventListener('DOMContentLoaded', function() {
    // Check if Framer Motion is available
    const hasFramerMotion = typeof window.framerMotion !== 'undefined';
    const hasGSAP = typeof gsap !== 'undefined';
    
    // Initialize floating background blobs
    initFloatingBlobs();
    
    // Initialize motion elements with Framer Motion if available
    if (hasFramerMotion) {
        initMotionElements();
    } else {
        // Fallback to basic animations if Framer Motion isn't loaded
        applyBasicAnimations();
    }
    
    // Initialize interactive elements
    initInteractiveElements();
    
    // Initialize task interaction features
    initTaskFeatures();
    
    // Initialize dropdown menus
    initDropdowns();
    
    // Counter animations for stat numbers
    animateCounters();
    
    // Functions
    
    // Initialize floating blob animations
    function initFloatingBlobs() {
        const blobs = document.querySelectorAll('.floating-blob');
        if (hasGSAP && blobs.length > 0) {
            blobs.forEach((blob, index) => {
                gsap.to(blob, {
                    x: Math.random() * 80 - 40,
                    y: Math.random() * 80 - 40,
                    duration: 8 + index * 2,
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut"
                });
            });
        }
    }
    
    // Initialize Framer Motion animations
    function initMotionElements() {
        const { motion } = window.framerMotion;
        const motionElements = document.querySelectorAll('.motion-element');
        
        // If no elements have the motion-element class, apply it to key UI elements
        if (motionElements.length === 0) {
            document.querySelectorAll('.glass-card, .welcome-section, .card-hover').forEach(
                (el, i) => {
                    el.classList.add('motion-element');
                    el.style.transitionDelay = `${i * 0.1}s`;
                }
            );
        }
        
        // Function to create staggered animations
        const createAnimations = (elements, options = {}) => {
            const defaults = {
                hidden: { opacity: 0, y: 20 },
                visible: { 
                    opacity: 1, 
                    y: 0,
                    transition: { 
                        duration: 0.6,
                        ease: [0.25, 0.1, 0.25, 1.0]
                    }
                }
            };
            
            const config = { ...defaults, ...options };
            
            elements.forEach((element, index) => {
                // Create a new motion component instance
                const motionInstance = motion(element, {
                    initial: config.hidden,
                    animate: config.visible,
                    transition: {
                        ...config.visible.transition,
                        delay: index * 0.15 // Staggered delay
                    }
                });
                
                // Setup intersection observer for triggering animations when visible
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            motionInstance.start("visible");
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });
                
                observer.observe(element);
            });
        };
        
        // Apply animations
        createAnimations(document.querySelectorAll('.motion-element'));
        
        // Add hover animations for event and task items
        const interactiveItems = document.querySelectorAll('.event-item, .task-item, .glass-card:not(.motion-element)');
        interactiveItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                motion(item, {
                    scale: 1.02,
                    transition: {
                        type: "spring",
                        stiffness: 300,
                        damping: 20
                    }
                });
            });
            
            item.addEventListener('mouseleave', () => {
                motion(item, {
                    scale: 1,
                    transition: {
                        type: "spring",
                        stiffness: 200,
                        damping: 25
                    }
                });
            });
        });
        
        // Enhance button hover effects with Framer Motion
        document.querySelectorAll('.btn-primary, button[type="submit"]').forEach(button => {
            button.addEventListener('mouseenter', () => {
                motion(button, {
                    scale: 1.05,
                    y: -2,
                    transition: {
                        type: "spring",
                        stiffness: 400,
                        damping: 10
                    }
                });
            });
            
            button.addEventListener('mouseleave', () => {
                motion(button, {
                    scale: 1,
                    y: 0,
                    transition: {
                        type: "spring",
                        stiffness: 400,
                        damping: 15
                    }
                });
            });
        });
    }
    
    // Fallback animations using CSS for browsers without Framer Motion
    function applyBasicAnimations() {
        // Apply fade-in animations
        document.querySelectorAll('.glass-card, .welcome-section, .card-hover').forEach(
            (el, i) => {
                el.classList.add('animate-fade-in-up');
                el.style.animationDelay = `${i * 0.1}s`;
            }
        );
        
        // Apply gradient animations
        document.body.classList.add('bg-gradient-animate');
        
        // Apply hover effects to interactive elements
        document.querySelectorAll('.glass-card, .event-item, .task-item').forEach(card => {
            card.classList.add('card-hover');
        });
        
        // Apply button hover animations
        document.querySelectorAll('button, a.py-2').forEach(btn => {
            btn.classList.add('btn-hover-scale');
        });
        
        // Apply shine effect to primary buttons
        document.querySelectorAll('.btn-primary, button[type="submit"]').forEach(button => {
            button.classList.add('shine');
        });
    }
    
    // Initialize interactive UI elements
    function initInteractiveElements() {
        // Apply gradient text effect to headings
        document.querySelectorAll('h1, h2.text-xl').forEach(heading => {
            heading.classList.add('gradient-text');
        });
        
        // Apply focus effects to form elements
        document.querySelectorAll('input, textarea, select').forEach(input => {
            input.classList.add('focus-ring-effect');
        });
        
        // Make cards into glass cards if they aren't already
        document.querySelectorAll('.bg-white\\/10.backdrop-blur-lg.rounded-xl').forEach(card => {
            if (!card.classList.contains('glass-card')) {
                card.classList.add('glass-card');
            }
        });
    }
    
    // Initialize task features with animations
    function initTaskFeatures() {
        // Task completion toggle functionality with animation
        document.querySelectorAll('.task-status-btn').forEach(button => {
            button.addEventListener('click', function() {
                const taskId = this.dataset.taskId;
                this.classList.toggle('checked');
                
                if (this.classList.contains('checked')) {
                    // Animate the button
                    if (hasFramerMotion) {
                        const { motion } = window.framerMotion;
                        motion(this, {
                            scale: [1, 1.5, 1],
                            transition: { duration: 0.4 }
                        });
                    } else if (hasGSAP) {
                        gsap.to(this, {
                            scale: 1.5,
                            duration: 0.2,
                            onComplete: () => {
                                gsap.to(this, {
                                    scale: 1,
                                    duration: 0.2
                                });
                            }
                        });
                    }
                    
                    this.innerHTML = `
                        <svg class="h-3 w-3 m-auto text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    `;
                    
                    // Update task status to completed via AJAX
                    fetch('api/update_task_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `task_id=${taskId}&status=completed`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Animate task completion
                            const taskItem = this.closest('.task-item') || this.closest('.p-6');
                            
                            if (hasFramerMotion) {
                                const { motion } = window.framerMotion;
                                motion(taskItem, {
                                    opacity: 0.5,
                                    y: [0, -10, 20],
                                    transition: { duration: 0.7 }
                                });
                            } else if (hasGSAP) {
                                gsap.to(taskItem, {
                                    opacity: 0.5,
                                    y: 20,
                                    duration: 0.7
                                });
                            }
                            
                            setTimeout(() => {
                                taskItem.remove();
                            }, 700);
                        }
                    });
                } else {
                    this.innerHTML = '';
                    
                    // Reset task status to pending
                    fetch('api/update_task_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `task_id=${taskId}&status=pending`
                    });
                }
            });
        });
    }
    
    // Initialize dropdown menus with animations
    function initDropdowns() {
        const dropdownButtons = document.querySelectorAll('.dropdown button');
        
        dropdownButtons.forEach(button => {
            button.addEventListener('click', function() {
                const dropdown = this.nextElementSibling;
                
                if (!dropdown) return;
                
                const isHidden = dropdown.classList.contains('hidden');
                
                // Hide all other dropdowns first
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== dropdown && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                    }
                });
                
                if (isHidden) {
                    dropdown.classList.remove('hidden');
                    
                    if (hasFramerMotion) {
                        const { motion } = window.framerMotion;
                        motion(dropdown, {
                            opacity: [0, 1],
                            y: [-10, 0],
                            transition: { duration: 0.3 }
                        });
                    } else if (hasGSAP) {
                        gsap.fromTo(dropdown, 
                            { opacity: 0, y: -10 },
                            { opacity: 1, y: 0, duration: 0.3 }
                        );
                    }
                } else {
                    if (hasFramerMotion) {
                        const { motion } = window.framerMotion;
                        motion(dropdown, {
                            opacity: [1, 0],
                            y: [0, -10],
                            transition: { duration: 0.3 },
                            onComplete: () => {
                                dropdown.classList.add('hidden');
                            }
                        });
                    } else if (hasGSAP) {
                        gsap.to(dropdown, {
                            opacity: 0, 
                            y: -10, 
                            duration: 0.3,
                            onComplete: () => {
                                dropdown.classList.add('hidden');
                            }
                        });
                    } else {
                        dropdown.classList.add('hidden');
                    }
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (!menu.classList.contains('hidden')) {
                        if (hasFramerMotion) {
                            const { motion } = window.framerMotion;
                            motion(menu, {
                                opacity: [1, 0],
                                y: [0, -10],
                                transition: { duration: 0.3 },
                                onComplete: () => {
                                    menu.classList.add('hidden');
                                }
                            });
                        } else {
                            menu.classList.add('hidden');
                        }
                    }
                });
            }
        });
    }
    
    // Animate counter numbers
    function animateCounters() {
        const counterElements = document.querySelectorAll('.counter-animate');
        
        if (counterElements.length > 0) {
            counterElements.forEach(counter => {
                const target = parseInt(counter.innerText);
                if (isNaN(target)) return;
                
                const increment = Math.max(1, target / 20);
                
                let current = 0;
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.innerText = Math.ceil(current);
                        setTimeout(updateCounter, 50);
                    } else {
                        counter.innerText = target;
                    }
                };
                
                updateCounter();
            });
        }
    }
});