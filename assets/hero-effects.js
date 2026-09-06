/**
 * Hero Section Interactive Mouse Particle Canvas Animation
 * The Code Munk
 */
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('heroLiquidCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width = (canvas.width = canvas.parentElement.offsetWidth || window.innerWidth);
    let height = (canvas.height = canvas.parentElement.offsetHeight || 500);

    const particles = [];
    const particleCount = 35;

    const mouse = {
        x: width / 2,
        y: height / 2,
        targetX: width / 2,
        targetY: height / 2,
        radius: 120
    };

    function resize() {
        if (!canvas.parentElement) return;
        width = canvas.width = canvas.parentElement.offsetWidth || window.innerWidth;
        height = canvas.height = canvas.parentElement.offsetHeight || 500;
    }

    window.addEventListener('resize', resize);

    const heroSection = canvas.closest('.hero');
    if (heroSection) {
        heroSection.addEventListener('mousemove', function (e) {
            const rect = canvas.getBoundingClientRect();
            mouse.targetX = e.clientX - rect.left;
            mouse.targetY = e.clientY - rect.top;
        });
    }

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.size = Math.random() * 3 + 1.5;
            this.baseX = this.x;
            this.baseY = this.y;
            this.vx = (Math.random() - 0.5) * 0.8;
            this.vy = (Math.random() - 0.5) * 0.8;
            this.opacity = Math.random() * 0.25 + 0.1;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            if (this.x < 0 || this.x > width) this.vx *= -1;
            if (this.y < 0 || this.y > height) this.vy *= -1;

            // Mouse interaction
            const dx = mouse.x - this.x;
            const dy = mouse.y - this.y;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist < mouse.radius) {
                const force = (mouse.radius - dist) / mouse.radius;
                this.x -= (dx / dist) * force * 3;
                this.y -= (dy / dist) * force * 3;
            }
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(17, 17, 17, ${this.opacity})`;
            ctx.fill();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        // Smooth mouse motion
        mouse.x += (mouse.targetX - mouse.x) * 0.08;
        mouse.y += (mouse.targetY - mouse.y) * 0.08;

        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();

            // Connect nearby particles with subtle lines
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < 100) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(17, 17, 17, ${0.08 * (1 - dist / 100)})`;
                    ctx.lineWidth = 0.8;
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(animate);
    }

    animate();
});
