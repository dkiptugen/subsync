<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? ($brand['name'] ?? config('app.name')) }}</title>
    <link rel="canonical" href="{{ $seo['canonical'] ?? route('landing') }}">
    @foreach($seo['meta'] ?? [] as $meta)
        <meta name="{{ $meta['name'] }}" content="{{ $meta['content'] }}">
    @endforeach
    @foreach($seo['open_graph'] ?? [] as $meta)
        <meta property="{{ $meta['property'] }}" content="{{ $meta['content'] }}">
    @endforeach
    @foreach($seo['twitter'] ?? [] as $meta)
        <meta name="{{ $meta['name'] }}" content="{{ $meta['content'] }}">
    @endforeach
    @if(!empty($seo['json_ld']))
        <script type="application/ld+json">@json($seo['json_ld'])</script>
    @endif
    <style>
        :root {
            --ink: #14213d;
            --muted: #64748b;
            --paper: #f7faf8;
            --line: #dce4df;
            --teal: #087f8c;
            --green: #2d936c;
            --coral: #e76f51;
            --gold: #f4a261;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--ink);
            background: var(--paper);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .landing-nav {
            position: fixed;
            z-index: 20;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 18px clamp(20px, 5vw, 72px);
            background: rgba(247, 250, 248, 0.9);
            border-bottom: 1px solid rgba(220, 228, 223, 0.8);
            backdrop-filter: blur(16px);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
        }

        .brand-mark img {
            width: 112px;
            height: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #385065;
            font-size: 0.95rem;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 18px;
            border: 1px solid var(--ink);
            border-radius: 0;
            font-weight: 800;
        }

        .button-primary {
            color: var(--white);
            background: var(--ink);
            box-shadow: 0 14px 28px rgba(20, 33, 61, 0.18);
        }

        .button-light {
            background: rgba(255, 255, 255, 0.7);
        }

        .hero {
            position: relative;
            min-height: 88vh;
            overflow: hidden;
            display: grid;
            align-items: end;
            padding: 128px clamp(20px, 6vw, 86px) 72px;
            background:
                linear-gradient(90deg, rgba(247, 250, 248, 0.98) 0%, rgba(247, 250, 248, 0.84) 42%, rgba(247, 250, 248, 0.34) 100%),
                linear-gradient(135deg, #e7f3ef 0%, #fff6eb 52%, #e9f1f3 100%);
        }

        .product-backdrop {
            position: absolute;
            inset: 96px -90px 34px 42%;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 16px;
            transform: rotate(-2deg);
            opacity: 0.96;
        }

        .product-panel {
            min-height: 320px;
            border: 1px solid rgba(20, 33, 61, 0.12);
            border-radius: 0;
            background: rgba(255, 255, 255, 0.82);
            box-shadow: 0 26px 70px rgba(20, 33, 61, 0.14);
            overflow: hidden;
        }

        .product-panel header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid var(--line);
        }

        .panel-pill {
            width: 112px;
            height: 10px;
            border-radius: 0;
            background: var(--teal);
        }

        .panel-dot {
            width: 10px;
            height: 10px;
            border-radius: 0;
            background: var(--coral);
        }

        .chart-bars {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 12px;
            align-items: end;
            height: 176px;
            padding: 26px 18px;
        }

        .chart-bars span {
            min-height: 34px;
            border-radius: 0;
            background: var(--green);
        }

        .chart-bars span:nth-child(2n) {
            background: var(--gold);
        }

        .chart-bars span:nth-child(3n) {
            background: var(--teal);
        }

        .feed {
            display: grid;
            gap: 12px;
            padding: 18px;
        }

        .feed span {
            height: 46px;
            border-radius: 0;
            background: #eef4f1;
            border-left: 5px solid var(--coral);
        }

        .hero-copy {
            position: relative;
            z-index: 2;
            width: min(720px, 100%);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 18px;
            color: var(--teal);
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0;
        }

        h1 {
            margin: 0;
            font-size: clamp(3.2rem, 8vw, 7.5rem);
            line-height: 0.92;
            letter-spacing: 0;
        }

        .hero-copy p {
            max-width: 650px;
            margin: 24px 0 0;
            color: #344a5e;
            font-size: clamp(1.05rem, 2vw, 1.35rem);
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 32px;
        }

        .metric-strip {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1px;
            width: min(760px, 100%);
            margin-top: 52px;
            border: 1px solid var(--line);
            border-radius: 0;
            overflow: hidden;
            background: var(--line);
        }

        .metric {
            padding: 18px;
            background: rgba(255, 255, 255, 0.78);
        }

        .metric strong {
            display: block;
            font-size: 1.45rem;
        }

        .metric span {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .section {
            padding: 76px clamp(20px, 6vw, 86px);
        }

        .section-heading {
            max-width: 760px;
            margin-bottom: 34px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3.6rem);
            letter-spacing: 0;
        }

        .section-heading p {
            color: var(--muted);
            line-height: 1.7;
            font-size: 1.05rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .feature-card,
        .plan-card,
        .ops-card {
            border: 1px solid var(--line);
            border-radius: 0;
            background: var(--white);
        }

        .feature-card {
            padding: 24px;
        }

        .feature-card .accent {
            width: 42px;
            height: 5px;
            margin-bottom: 18px;
            border-radius: 0;
            background: var(--coral);
        }

        .feature-card:nth-child(2n) .accent {
            background: var(--teal);
        }

        .feature-card:nth-child(3n) .accent {
            background: var(--gold);
        }

        .feature-card h3,
        .plan-card h3,
        .ops-card h3 {
            margin: 0 0 10px;
            font-size: 1.18rem;
        }

        .feature-card p,
        .ops-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .plans {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .plan-card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 520px;
            padding: 26px;
        }

        .plan-card.is-popular {
            border-color: var(--teal);
            box-shadow: 0 18px 46px rgba(8, 127, 140, 0.14);
        }

        .badge {
            align-self: flex-start;
            margin-bottom: 18px;
            padding: 7px 10px;
            border-radius: 0;
            color: var(--teal);
            background: #e6f3f4;
            font-weight: 800;
            font-size: 0.78rem;
        }

        .price {
            margin: 16px 0 8px;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
        }

        .interval {
            color: var(--muted);
            font-weight: 700;
        }

        .plan-card ul {
            display: grid;
            gap: 12px;
            margin: 26px 0;
            padding: 0;
            list-style: none;
        }

        .plan-card li {
            display: grid;
            grid-template-columns: 20px 1fr;
            gap: 10px;
            color: #344a5e;
            line-height: 1.45;
        }

        .check {
            width: 18px;
            height: 18px;
            border-radius: 0;
            background: var(--green);
            color: var(--white);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 900;
        }

        .plan-card .button {
            margin-top: auto;
            width: 100%;
        }

        .ops-band {
            background: #102133;
            color: var(--white);
        }

        .ops-band .section-heading p {
            color: #b9c6ce;
        }

        .ops-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .ops-card {
            padding: 24px;
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.16);
        }

        .ops-card p {
            color: #c8d4d9;
        }

        .footer-cta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            border-top: 1px solid var(--line);
        }

        .footer-cta h2 {
            margin: 0 0 10px;
            font-size: clamp(2rem, 4vw, 3.4rem);
        }

        .footer-cta p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        @media (max-width: 980px) {
            .product-backdrop {
                inset: 92px -170px 28px 26%;
                opacity: 0.42;
            }

            .feature-grid,
            .plans,
            .ops-grid {
                grid-template-columns: 1fr;
            }

            .plan-card {
                min-height: auto;
            }
        }

        @media (max-width: 720px) {
            .nav-links {
                display: none;
            }

            .landing-nav {
                padding: 14px 18px;
            }

            .brand-mark img {
                width: 92px;
            }

            .hero {
                min-height: 92vh;
                padding: 112px 20px 54px;
            }

            .product-backdrop {
                inset: 80px -300px 80px 12%;
            }

            .metric-strip {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 58px 20px;
            }
        }
    </style>
</head>
<body>
<nav class="landing-nav">
    <a class="brand-mark" href="{{ route('landing') }}">
        <img src="{{ asset('assets/img/logo.png') }}" alt="{{ $brand['name'] ?? config('app.name') }}">
    </a>
    <div class="nav-links" aria-label="Primary navigation">
        @foreach($navigation as $item)
            <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
        @endforeach
    </div>
    <div class="nav-actions">
        <a class="button button-light" href="{{ route('login') }}">Sign in</a>
        <a class="button button-primary" href="#plans">View options</a>
    </div>
</nav>

<section class="hero">
    <div class="product-backdrop" aria-hidden="true">
        <div class="product-panel">
            <header>
                <span class="panel-pill"></span>
                <span class="panel-dot"></span>
            </header>
            <div class="chart-bars">
                <span style="height: 38%"></span>
                <span style="height: 58%"></span>
                <span style="height: 72%"></span>
                <span style="height: 48%"></span>
                <span style="height: 86%"></span>
                <span style="height: 64%"></span>
                <span style="height: 92%"></span>
            </div>
        </div>
        <div class="product-panel">
            <header>
                <span class="panel-pill"></span>
                <span class="panel-dot"></span>
            </header>
            <div class="feed">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>

    <div class="hero-copy">
        <p class="eyebrow">{{ $brand['tagline'] ?? 'Enterprise subscription infrastructure for media companies.' }}</p>
        <h1>{{ $brand['name'] ?? config('app.name') }}</h1>
        <p>{{ $brand['description'] ?? '' }}</p>
        <div class="hero-actions">
            <a class="button button-primary" href="#plans">Compare options</a>
            <a class="button button-light" href="{{ route('login') }}">Open workspace</a>
        </div>
        <div class="metric-strip">
            @foreach($metrics as $metric)
                <div class="metric">
                    <strong>{{ $metric['value'] }}</strong>
                    <span>{{ $metric['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section" id="platform">
    <div class="section-heading">
        <h2>Subscription infrastructure for serious media operators.</h2>
        <p>Product, billing, access, finance, and subscriber teams stay connected, so publishers can run reader revenue across publications, payment channels, and corporate accounts without fragmented back-office tools.</p>
    </div>
    <div class="feature-grid">
        @foreach($features as $feature)
            <article class="feature-card">
                <div class="accent"></div>
                <h3>{{ $feature['name'] }}</h3>
                <p>{{ $feature['description'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="section" id="plans">
    <div class="section-heading">
        <h2>Operating models for every stage of media growth.</h2>
        <p>Start with a focused publication, scale into multi-product reader revenue, or run governed enterprise subscription operations across markets and teams.</p>
    </div>
    <div class="plans">
        @foreach($plans as $plan)
            <article class="plan-card @if($plan['popular']) is-popular @endif">
                @if($plan['badge'] !== '')
                    <span class="badge">{{ $plan['badge'] }}</span>
                @endif
                <h3>{{ $plan['name'] }}</h3>
                <p>{{ $plan['summary'] }}</p>
                <div class="price">{{ $plan['price'] }}</div>
                <div class="interval">per {{ $plan['interval'] }} @if($plan['trial_days'] > 0) - {{ $plan['trial_days'] }} day trial @endif</div>
                <ul>
                    @foreach($plan['features'] as $feature)
                        <li><span class="check">&#10003;</span><span>{{ $feature['label'] }}</span></li>
                    @endforeach
                </ul>
                <a class="button @if($plan['popular']) button-primary @else button-light @endif" href="{{ route('login') }}">{{ $plan['cta'] }}</a>
            </article>
        @endforeach
    </div>
</section>

<section class="section ops-band" id="operations">
    <div class="section-heading">
        <h2>Built for the operational core of reader revenue.</h2>
        <p>SubSync is shaped for the work that happens between checkout and customer success: approvals, reconciliation, subscriber support, and product entitlement.</p>
    </div>
    <div class="ops-grid">
        <article class="ops-card">
            <h3>Finance-ready billing</h3>
            <p>Keep transaction state, receipts, payment metadata, subscription approvals, and reconciliation workflows close to every subscription product.</p>
        </article>
        <article class="ops-card">
            <h3>Publisher-grade controls</h3>
            <p>Give operators, finance teams, sales teams, and administrators the right access through roles, permissions, and audit-ready activity.</p>
        </article>
        <article class="ops-card">
            <h3>Media ecosystem fit</h3>
            <p>Connect product sites, payment providers, finance workflows, corporate access rules, and subscriber support around one operational source of truth.</p>
        </article>
    </div>
</section>

<section class="section footer-cta">
    <div>
        <h2>Ready to run reader revenue with authority?</h2>
        <p>Adopt the platform already used by Nation Media Group, then adapt the workspace around your publications, subscribers, finance flows, and access rules.</p>
    </div>
    <a class="button button-primary" href="{{ route('login') }}">Sign in to workspace</a>
</section>
</body>
</html>
