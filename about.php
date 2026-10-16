<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About • Quantum Minds United</title>
  <meta name="description" content="Quantum Minds United: Awaken. Create. Unite. A command deck for consciousness exploration, community, and the impossible made real." />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --night: #0a0f1f;
      --aurora-200: #aee2ff;
      --aurora-300: #76ccff;
      --aurora-500: #2297ff;
      --aurora-600: #1778db;
      --quantum-purple: #8b5cf6;
      --quantum-cyan: #06b6d4;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: linear-gradient(135deg, #0a0f1f 0%, #0f1419 100%);
      color: white;
      line-height: 1.6;
      padding: 32px 24px;
      min-height: 100vh;
    }

    .font-display { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }

    .container { max-width: 900px; margin: 0 auto; }

    /* Hero Badge */
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;
      background: rgba(139,92,246,0.1); border: 1px solid rgba(139,92,246,0.3);
      border-radius: 50px; color: var(--aurora-200); font-size: 12px; font-weight: 500;
      margin-bottom: 24px; animation: pulse-glow 3s ease-in-out infinite;
    }
    @keyframes pulse-glow { 0%,100%{box-shadow:0 0 20px rgba(139,92,246,0.2);} 50%{box-shadow:0 0 30px rgba(139,92,246,0.4);} }
    .pulse-dot { width: 6px; height: 6px; background: var(--aurora-500); border-radius: 50%; animation: pulse-dot 2s ease-in-out infinite; }
    @keyframes pulse-dot { 0%,100%{opacity:1;} 50%{opacity:.5;} }

    /* Typography */
    .hero-title {
      font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; line-height: 1.1; margin-bottom: 16px;
      background: linear-gradient(135deg, white, var(--aurora-300), var(--quantum-purple));
      background-size: 200% 200%; -webkit-background-clip: text; background-clip: text;
      -webkit-text-fill-color: transparent; color: transparent;
      animation: gradient-shift 4s ease-in-out infinite;
    }
    @keyframes gradient-shift { 0%,100%{background-position:0% 50%;} 50%{background-position:100% 50%;} }

    .hero-subtitle { font-size: 1rem; color: rgba(255,255,255,0.9); margin-bottom: 32px; }
    .section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 16px; color: white; }
    .section-text { color: rgba(255,255,255,0.9); line-height: 1.7; margin-bottom: 16px; }

    /* Glass cards */
    .glass {
      background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
      border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(12px);
      border-radius: 16px; padding: 24px; margin-bottom: 24px;
      transition: all 0.3s ease;
    }
    .glass:hover {
      background: linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04));
      border-color: rgba(34,151,255,0.2);
    }

    /* Grid + cards */
    .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 32px; }
    .card { padding: 20px; background: rgba(15,20,31,0.4); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; }
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .card-icon {
      width: 32px; height: 32px; background: rgba(34,151,255,0.15); border: 1px solid rgba(34,151,255,0.3);
      border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--aurora-300);
    }
    .card h3 { font-size: 1rem; font-weight: 600; margin: 0; }
    .card p { color: rgba(255,255,255,0.8); font-size: 0.875rem; line-height: 1.5; margin: 0; }

    /* Feature list */
    .feature-list { display: grid; gap: 12px; }
    .feature-item {
      padding: 12px; background: rgba(15,20,31,0.3); border: 1px solid rgba(255,255,255,0.05);
      border-radius: 8px; display: flex; align-items: flex-start; gap: 10px; font-size: 0.875rem;
    }
    .feature-icon { font-size: 1rem; margin-top: 2px; }

    /* CTA Buttons */
    .cta-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 24px; }
    .btn {
      display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px;
      text-decoration: none; font-weight: 600; font-size: 0.875rem;
      transition: all 0.3s ease; position: relative; overflow: hidden;
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--aurora-600), var(--quantum-purple)); color: white;
      box-shadow: 0 4px 20px rgba(34,151,255,0.3);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, var(--aurora-500), var(--quantum-cyan));
      transform: translateY(-1px); box-shadow: 0 6px 25px rgba(34,151,255,0.4);
    }
    .btn-secondary {
      background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); color: white;
    }
    .btn-secondary:hover {
      background: rgba(255,255,255,0.15); border-color: rgba(34,151,255,0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
      body { padding: 16px 12px; }
      .grid { grid-template-columns: 1fr; }
      .cta-buttons { flex-direction: column; align-items: center; }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Hero -->
    <div style="text-align: center; margin-bottom: 32px;">
      <div class="hero-badge"><div class="pulse-dot"></div>About Quantum Minds United</div>
      <h1 class="hero-title font-display">Awaken. Create. Unite.</h1>
      <p class="hero-subtitle">A command deck for consciousness exploration, community, and the impossible made real.</p>
    </div>

    <!-- Mission -->
    <div class="glass">
      <h2 class="section-title font-display">Our Mission</h2>
      <p class="section-text">
        Quantum Minds United is where consciousness meets technology, where ancient wisdom merges with cutting-edge tools, 
        and where the impossible becomes inevitable. We're building a community for the awakening mind—those ready to 
        explore the edges of reality and co-create a new paradigm.
      </p>
      <p class="section-text" style="margin-bottom: 0;">
        Through interactive experiences, deep research, and collective exploration, we're mapping the territories of 
        human potential that mainstream science hasn't caught up to yet.
      </p>
    </div>

    <!-- What We Offer -->
    <h2 class="section-title font-display">What We Offer</h2>
    <div class="grid">
      <div class="card">
        <div class="card-header">
          <div class="card-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M3 20v-2a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v2" stroke="currentColor"/>
              <circle cx="12" cy="7" r="3" stroke="currentColor"/>
            </svg>
          </div>
          <h3>Living Community</h3>
        </div>
        <p>Join conversations that matter in our forum and Discord. Share insights, ask questions, and connect with fellow explorers on the path of conscious evolution.</p>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M4 12h4l2 4 4-8 3 6h3" stroke="currentColor"/>
            </svg>
          </div>
          <h3>Consciousness Tools</h3>
        </div>
        <p>Experience our Hemi-Lab brainwave generator, chakra binaural beats, and Gateway Trainer—precision instruments for exploring altered states of consciousness.</p>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M4 5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5z" stroke="currentColor"/>
              <path d="M8 7h8M8 11h8M8 15h5" stroke="currentColor"/>
            </svg>
          </div>
          <h3>Knowledge Archive</h3>
        </div>
        <p>Dive into our curated library of PDFs, audiobooks, deep research documents, and AI-generated podcasts exploring the frontiers of human potential.</p>
      </div>
    </div>

    <!-- Philosophy -->
    <div class="glass">
      <h2 class="section-title font-display">Our Philosophy</h2>
      <div class="feature-list">
        <div class="feature-item">
          <div class="feature-icon">🌌</div>
          <div>
            <strong>Consciousness First:</strong> Reality is far stranger and more malleable than consensus science suggests. We explore the observer effect in daily life.
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">🔬</div>
          <div>
            <strong>Experiential Research:</strong> Don't just read about altered states—experience them. Our tools are designed for direct exploration of consciousness.
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">🧠</div>
          <div>
            <strong>Neurodivergent Welcome:</strong> ADHD, autism, and other forms of neurodivergence aren't disorders—they're evolutionary adaptations for the emerging paradigm.
          </div>
        </div>
        <div class="feature-item">
          <div class="feature-icon">⚡</div>
          <div>
            <strong>Technology + Spirit:</strong> We bridge ancient wisdom and cutting-edge tech, using AI, binaural beats, and digital tools to accelerate awakening.
          </div>
        </div>
      </div>
    </div>

    <!-- Creator -->
    <div class="glass">
      <h2 class="section-title font-display">The Creator</h2>
      <p class="section-text">
        Quantum Minds United was created by <strong>Meistro</strong> (Mark), the mind behind Eli GPT and the Awakening Mind GPT. 
        A consciousness researcher, AI developer, and bridge between worlds, Meistro combines deep technical knowledge with 
        mystical insight to create tools and experiences that expand human potential.
      </p>
      <p class="section-text" style="margin-bottom: 0;">
        Through years of exploring altered states, working with AI, and diving deep into the nature of reality, 
        Meistro has developed a unique approach to consciousness exploration that you'll find nowhere else.
      </p>
    </div>

    <!-- CTA -->
    <div style="text-align: center;">
      <h2 class="section-title font-display">Ready to Begin?</h2>
      <p class="section-text">
        The portal is open. Your consciousness is the key. Whether you're here for the tools, the community, 
        or the impossible adventures, you're exactly where you need to be.
      </p>
      <div class="cta-buttons">
        <a href="https://forum.quantummindsunited.com/" class="btn btn-primary" target="_blank" rel="noopener">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M3 20v-2a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v2" stroke="currentColor"/>
            <circle cx="12" cy="7" r="3" stroke="currentColor"/>
          </svg>
          Join the Community
        </a>
        <a href="https://quantummindsunited.com/hemi-lab/" class="btn btn-secondary" target="_blank" rel="noopener">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path d="M4 12h4l2 4 4-8 3 6h3" stroke="currentColor"/>
          </svg>
          Try the Tools
        </a>
      </div>
    </div>
  </div>
</body>
</html>
