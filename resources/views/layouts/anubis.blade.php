<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Anubis License Portal')</title>
    <style>
        :root {
            --anx-black: #0b0b0d;
            --anx-black-soft: #151519;
            --anx-surface: #16161b;
            --anx-surface-2: #1a1a1f;
            --anx-gold: #f2c287;
            --anx-gold-light: #ffda9a;
            --anx-gold-deep: #9b7a50;
            --anx-glass-bg: rgba(22, 22, 27, 0.55);
            --anx-glass-bg-strong: rgba(22, 22, 27, 0.85);
            --anx-glass-border: rgba(242, 194, 135, 0.18);
            --anx-glass-border-strong: rgba(242, 194, 135, 0.4);
            --anx-glass-blur: 16px;
            --anx-radius: 16px;
            --anx-radius-sm: 10px;
            --anx-shadow-soft: 0 8px 24px rgba(0,0,0,0.35);
            --anx-shadow-lift: 0 16px 40px rgba(0,0,0,0.5);
            --anx-glow-gold: 0 0 24px rgba(242,194,135,0.25);
            --anx-transition: all .25s cubic-bezier(.2,.8,.2,1);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(242,194,135,0.08), transparent),
                var(--anx-black);
            color: var(--anx-gold);
        }
        a { color: var(--anx-gold-light); text-decoration: none; }
        a:hover { color: #fff; }

        /* ===== Top nav (used by admin + portal shells) ===== */
        .anx-topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 28px;
            background: var(--anx-glass-bg-strong);
            -webkit-backdrop-filter: blur(var(--anx-glass-blur));
            backdrop-filter: blur(var(--anx-glass-blur));
            border-bottom: 1px solid var(--anx-glass-border);
        }
        .anx-brand { font-weight: 800; letter-spacing: .5px; color: var(--anx-gold-light); font-size: 1.1rem; }
        .anx-brand span { color: var(--anx-gold-deep); font-weight: 400; }
        .anx-nav { display: flex; gap: 6px; align-items: center; }
        .anx-nav a, .anx-nav button {
            padding: 8px 14px; border-radius: var(--anx-radius-sm); font-size: 13px; font-weight: 600;
            background: transparent; border: 1px solid transparent; color: var(--anx-gold);
            cursor: pointer; transition: var(--anx-transition);
        }
        .anx-nav a:hover, .anx-nav button:hover { background: var(--anx-glass-bg); border-color: var(--anx-glass-border); color: var(--anx-gold-light); }
        .anx-nav a.active { background: linear-gradient(135deg, var(--anx-gold-light), var(--anx-gold-deep)); color: var(--anx-black-soft); }

        .anx-container { max-width: 1100px; margin: 0 auto; padding: 32px 24px 60px; }
        .anx-container.narrow { max-width: 440px; padding-top: 64px; }

        /* ===== Cards ===== */
        .anx-card {
            background: var(--anx-glass-bg);
            -webkit-backdrop-filter: blur(var(--anx-glass-blur));
            backdrop-filter: blur(var(--anx-glass-blur));
            border: 1px solid var(--anx-glass-border);
            border-radius: var(--anx-radius);
            box-shadow: var(--anx-shadow-soft);
            padding: 24px 26px;
            margin-bottom: 20px;
        }
        .anx-card h2, .anx-card h3 { margin-top: 0; color: var(--anx-gold-light); }

        /* ===== Forms ===== */
        .anx-field { margin-bottom: 16px; }
        .anx-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--anx-gold-deep); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }
        .anx-field input, .anx-field select, .anx-field textarea {
            width: 100%; padding: 11px 13px; background: var(--anx-surface-2);
            border: 1px solid var(--anx-glass-border); border-radius: var(--anx-radius-sm);
            color: var(--anx-gold-light); font-size: 14px; transition: var(--anx-transition);
        }
        .anx-field input:focus, .anx-field select:focus, .anx-field textarea:focus {
            outline: none; border-color: var(--anx-gold); box-shadow: var(--anx-glow-gold);
        }
        .anx-error { color: #ff8c8c; font-size: 12.5px; margin-top: 5px; }
        .anx-hint { color: #9b8a6a; font-size: 12.5px; margin: -4px 0 16px; }

        /* ===== Buttons ===== */
        .anx-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 10px 18px; border-radius: var(--anx-radius-sm); border: none;
            font-weight: 700; font-size: 13.5px; cursor: pointer; text-decoration: none;
            transition: var(--anx-transition);
        }
        .anx-btn-primary { background: linear-gradient(135deg, var(--anx-gold-light), var(--anx-gold-deep)); color: var(--anx-black-soft); }
        .anx-btn-primary:hover { box-shadow: var(--anx-glow-gold); filter: brightness(1.08); color: var(--anx-black-soft); }
        .anx-btn-ghost { background: var(--anx-glass-bg); border: 1px solid var(--anx-glass-border); color: var(--anx-gold); }
        .anx-btn-ghost:hover { border-color: var(--anx-glass-border-strong); color: var(--anx-gold-light); }
        .anx-btn-danger { background: rgba(74,26,26,0.6); border: 1px solid rgba(232,93,93,0.4); color: #ff8c8c; }
        .anx-btn-danger:hover { background: rgba(74,26,26,0.9); }
        .anx-btn-block { width: 100%; }
        .anx-btn-sm { padding: 6px 12px; font-size: 12px; }

        /* ===== Table ===== */
        .anx-table { width: 100%; border-collapse: collapse; }
        .anx-table th {
            text-align: left; padding: 12px 14px; font-size: 11.5px; text-transform: uppercase;
            letter-spacing: .04em; color: var(--anx-gold-deep); border-bottom: 1px solid var(--anx-glass-border);
        }
        .anx-table td { padding: 12px 14px; font-size: 13.5px; border-bottom: 1px solid rgba(242,194,135,0.08); }
        .anx-table tr:hover td { background: var(--anx-glass-bg); }
        .anx-table code { background: var(--anx-surface-2); padding: 3px 7px; border-radius: 6px; font-size: 12px; }

        /* ===== Badges ===== */
        .anx-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
        .anx-badge-active { background: rgba(93,184,93,0.15); color: #5db85d; }
        .anx-badge-trial { background: rgba(93,184,232,0.15); color: #5db8e8; }
        .anx-badge-past_due { background: rgba(232,184,93,0.15); color: #e8b85d; }
        .anx-badge-canceled { background: rgba(232,93,93,0.15); color: #e85d5d; }

        /* ===== Stat tiles (for the analytics dashboard, phase C) ===== */
        .anx-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .anx-stat-tile { background: var(--anx-glass-bg); border: 1px solid var(--anx-glass-border); border-radius: var(--anx-radius); padding: 18px 20px; }
        .anx-stat-tile .num { font-size: 1.8rem; font-weight: 800; color: var(--anx-gold-light); }
        .anx-stat-tile .label { font-size: 12px; color: var(--anx-gold-deep); text-transform: uppercase; letter-spacing: .04em; margin-top: 4px; }

        /* ===== Alerts ===== */
        .anx-alert { padding: 12px 16px; border-radius: var(--anx-radius-sm); margin-bottom: 18px; font-size: 13.5px; }
        .anx-alert-success { background: rgba(93,184,93,0.12); border: 1px solid rgba(93,184,93,0.3); color: #7ed07e; }
        .anx-alert-error { background: rgba(232,93,93,0.12); border: 1px solid rgba(232,93,93,0.3); color: #ff8c8c; }

        .anx-empty { text-align: center; padding: 50px 20px; color: var(--anx-gold-deep); }
        .anx-flex-between { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
