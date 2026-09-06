(function () {
    const canvas = document.getElementById('heroLiquidCanvas');
    if (!canvas) return;

    const heroSection = canvas.closest('.hero') || canvas.parentElement;
    const ctx = canvas.getContext('2d');

    let width = 0, height = 0;
    let mouse = { x: -1000, y: -1000, targetX: -1000, targetY: -1000, radius: 180, active: false };
    let ripples = [];

    // Liquid Nodes Array for Fluid Wave
    const numNodes = 55;
    let nodes = [];

    function resize() {
        if (!heroSection) return;
        width = canvas.width = heroSection.offsetWidth;
        height = canvas.height = heroSection.offsetHeight;
        initNodes();
    }

    function initNodes() {
        nodes = [];
        const spacing = width / (numNodes - 1);
        const baseY = height * 0.72; // Liquid baseline level

        for (let i = 0; i < numNodes; i++) {
            nodes.push({
                x: i * spacing,
                y: baseY,
                baseY: baseY,
                vy: 0,
                vx: 0,
                friction: 0.88,
                spring: 0.045
            });
        }
    }

    // Document-level Mouse Interaction for seamless tracking across all hero elements
    document.addEventListener('mousemove', (e) => {
        if (!heroSection) return;
        const rect = heroSection.getBoundingClientRect();
        if (
            e.clientX >= rect.left &&
            e.clientX <= rect.right &&
            e.clientY >= rect.top &&
            e.clientY <= rect.bottom
        ) {
            mouse.targetX = e.clientX - rect.left;
            mouse.targetY = e.clientY - rect.top;
            mouse.active = true;
        } else {
            mouse.active = false;
        }
    });

    document.addEventListener('mouseleave', () => {
        mouse.active = false;
        mouse.targetX = -1000;
        mouse.targetY = -1000;
    });

    heroSection.addEventListener('click', (e) => {
        const rect = heroSection.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const clickY = e.clientY - rect.top;
        
        ripples.push({
            x: clickX,
            y: clickY,
            radius: 8,
            maxRadius: 220,
            strength: 34,
            alpha: 1
        });
    });

    // Touch Support
    heroSection.addEventListener('touchmove', (e) => {
        if (e.touches.length > 0) {
            const rect = heroSection.getBoundingClientRect();
            mouse.targetX = e.touches[0].clientX - rect.left;
            mouse.targetY = e.touches[0].clientY - rect.top;
            mouse.active = true;
        }
    }, { passive: true });

    function update() {
        // Smooth mouse position interpolating
        mouse.x += (mouse.targetX - mouse.x) * 0.18;
        mouse.y += (mouse.targetY - mouse.y) * 0.18;

        // Update liquid nodes physics
        for (let i = 0; i < nodes.length; i++) {
            const node = nodes[i];
            
            // Distance to mouse
            const dx = node.x - mouse.x;
            const dy = node.y - mouse.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < mouse.radius && mouse.active) {
                const force = (1 - dist / mouse.radius) * 16;
                const angle = Math.atan2(dy, dx);
                node.vy += Math.sin(angle) * force;
                node.vx += Math.cos(angle) * force * 0.6;
            }

            // Click ripples physics
            for (let r = 0; r < ripples.length; r++) {
                const rip = ripples[r];
                const rdx = node.x - rip.x;
                const rdist = Math.abs(rdx);
                if (Math.abs(rdist - rip.radius) < 40) {
                    node.vy += Math.sin(rdx * 0.05) * rip.strength * 0.22;
                }
            }

            // Spring return to base
            const dBaseY = node.baseY - node.y;
            node.vy += dBaseY * node.spring;
            node.vy *= node.friction;
            node.y += node.vy;

            // Horizontal dampening
            node.vx *= 0.9;
            node.x += node.vx;
        }

        // Update click ripples expansion
        for (let r = ripples.length - 1; r >= 0; r--) {
            const rip = ripples[r];
            rip.radius += 5.5;
            rip.alpha -= 0.016;
            if (rip.alpha <= 0 || rip.radius >= rip.maxRadius) {
                ripples.splice(r, 1);
            }
        }
    }

    function render() {
        ctx.clearRect(0, 0, width, height);

        // Draw Liquid Spline Mesh
        if (nodes.length > 1) {
            ctx.beginPath();
            ctx.moveTo(0, height);
            ctx.lineTo(nodes[0].x, nodes[0].y);

            for (let i = 0; i < nodes.length - 1; i++) {
                const curr = nodes[i];
                const next = nodes[i + 1];
                const xc = (curr.x + next.x) / 2;
                const yc = (curr.y + next.y) / 2;
                ctx.quadraticCurveTo(curr.x, curr.y, xc, yc);
            }

            ctx.lineTo(width, height);
            ctx.closePath();

            // Liquid Gradient Fill (Brand Red & Gold luminous blend)
            const grad = ctx.createLinearGradient(0, height * 0.55, 0, height);
            grad.addColorStop(0, 'rgba(242, 184, 75, 0.18)'); // Brand gold glow
            grad.addColorStop(0.4, 'rgba(229, 46, 46, 0.08)');  // Brand red subtle mist
            grad.addColorStop(1, 'rgba(255, 255, 255, 0.01)');
            
            ctx.fillStyle = grad;
            ctx.fill();

            // Liquid Top Edge Wave Line
            ctx.beginPath();
            ctx.moveTo(nodes[0].x, nodes[0].y);
            for (let i = 0; i < nodes.length - 1; i++) {
                const curr = nodes[i];
                const next = nodes[i + 1];
                const xc = (curr.x + next.x) / 2;
                const yc = (curr.y + next.y) / 2;
                ctx.quadraticCurveTo(curr.x, curr.y, xc, yc);
            }
            ctx.strokeStyle = 'rgba(242, 184, 75, 0.55)';
            ctx.lineWidth = 2.5;
            ctx.stroke();
        }

        // Draw Mouse Liquid Aura / Interactive Cursor Halo
        if (mouse.active && mouse.x > 0 && mouse.y > 0) {
            const auraGrad = ctx.createRadialGradient(mouse.x, mouse.y, 0, mouse.x, mouse.y, mouse.radius * 0.85);
            auraGrad.addColorStop(0, 'rgba(242, 184, 75, 0.28)');
            auraGrad.addColorStop(0.45, 'rgba(229, 46, 46, 0.12)');
            auraGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');

            ctx.beginPath();
            ctx.arc(mouse.x, mouse.y, mouse.radius * 0.85, 0, Math.PI * 2);
            ctx.fillStyle = auraGrad;
            ctx.fill();
        }

        // Draw Expanding Click Ripples
        for (let r = 0; r < ripples.length; r++) {
            const rip = ripples[r];
            ctx.beginPath();
            ctx.arc(rip.x, rip.y, rip.radius, 0, Math.PI * 2);
            ctx.strokeStyle = `rgba(242, 184, 75, ${Math.max(0, rip.alpha * 0.6)})`;
            ctx.lineWidth = 2.2;
            ctx.stroke();
        }
    }

    function loop() {
        update();
        render();
        requestAnimationFrame(loop);
    }

    window.addEventListener('resize', resize);
    window.addEventListener('load', resize);
    setTimeout(resize, 100);
    setTimeout(resize, 500);
    loop();
})();
