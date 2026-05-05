<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumière PMS — Sign In</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --gold: #C9A84C;
            --gold-light: #E8C97A;
            --deep: #0A0A0F;
            --panel: #12121A;
            --border: #2A2A3A;
            --text: #E8E4DC;
            --muted: #7A7A8E;
        }

        html,
        body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--deep);
            color: var(--text)
        }

        body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh
        }

        /* Left panel */
        .left {
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, #0D0B08 0%, #1A1208 50%, #0D0A06 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 60px;
        }

        .left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23C9A84C' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: .5;
        }

        .ornament {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 340px;
            height: 340px;
            border: 1px solid rgba(201, 168, 76, .12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ornament::before {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            border: 1px solid rgba(201, 168, 76, .18);
            border-radius: 50%;
        }

        .ornament::after {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(201, 168, 76, .08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hotel-icon {
            position: relative;
            z-index: 2;
            font-family: 'Cormorant Garamond', serif;
            font-size: 72px;
            color: var(--gold);
            font-weight: 300;
            letter-spacing: 4px;
            text-shadow: 0 0 60px rgba(201, 168, 76, .3);
        }

        .left-content {
            position: relative;
            z-index: 2
        }

        .tagline {
            font-family: 'Cormorant Garamond', serif;
            font-size: 38px;
            font-weight: 300;
            line-height: 1.2;
            color: var(--text);
            margin-bottom: 16px;
            letter-spacing: .5px;
        }

        .tagline em {
            color: var(--gold);
            font-style: italic
        }

        .sub {
            font-size: 13px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase
        }

        /* Right panel */
        .right {
            background: var(--panel);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            border-left: 1px solid var(--border);
        }

        .form-wrap {
            width: 100%;
            max-width: 380px
        }

        .brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .brand-sub {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 48px
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 40px;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: var(--border)
        }

        .divider-diamond {
            width: 6px;
            height: 6px;
            background: var(--gold);
            transform: rotate(45deg);
            flex-shrink: 0;
        }

        h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            font-weight: 300;
            margin-bottom: 32px;
            letter-spacing: .5px;
        }

        .field {
            margin-bottom: 20px
        }

        label {
            display: block;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px
        }

        input[type=text],
        input[type=password] {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid var(--border);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
            border-radius: 2px;
        }

        input:focus {
            border-color: var(--gold)
        }

        input::placeholder {
            color: var(--muted)
        }

        .role-select {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid var(--border);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color .2s;
            appearance: none;
            border-radius: 2px;
            cursor: pointer;
        }

        .role-select option {
            background: var(--panel)
        }

        .role-wrap {
            position: relative
        }

        .role-wrap::after {
            content: '▾';
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
            font-size: 12px;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
            background-size: 200% 100%;
            background-position: 0% 0%;
            border: none;
            color: #0A0A0F;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-position .4s, transform .15s;
            border-radius: 2px;
        }

        .btn-login:hover {
            background-position: 100% 0%;
            transform: translateY(-1px)
        }

        .btn-login:active {
            transform: translateY(0)
        }

        .footer-note {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
        }

        .error-msg {
            background: rgba(220, 60, 60, .1);
            border: 1px solid rgba(220, 60, 60, .3);
            color: #E87070;
            font-size: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            border-radius: 2px;
            display: none;
        }

        @media(max-width:768px) {
            body {
                grid-template-columns: 1fr
            }

            .left {
                display: none
            }

            .right {
                padding: 40px 24px
            }
        }
    </style>
</head>

<body>
    <div class="left">
        <div class="ornament">
            <span class="hotel-icon">✦</span>
        </div>
        <div class="left-content">
            <h1 class="tagline">Hospitality<br>managed with <em>precision</em></h1>
            <p class="sub">Property Management System — v2.0</p>
        </div>
    </div>

    <div class="right">
        <div class="form-wrap">
            <div class="brand">Lumière</div>
            <div class="brand-sub">Boutique Hotel PMS</div>
            <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-diamond"></div>
                <div class="divider-line"></div>
            </div>
            <h2>Welcome back</h2>

            <div class="error-msg" id="errMsg">Invalid credentials. Please try again.</div>
            <?php if (isset($error)): ?>
                <div class="error-msg" style="display:block">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form action="?url=/login" method="POST" onsubmit="return validate(event)">
                <div class="field">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter your username" required>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="field">
                    <!-- <label>Role</label>
          <div class="role-wrap">
            <select class="role-select" name="role">
              <option value="">— Select Role —</option>
              <option value="FrontDesk">Front-Desk Agent</option>
              <option value="Housekeeper">Housekeeper</option>
              <option value="HKSupervisor">HK Supervisor</option>
              <option value="Accountant">Accountant</option>
              <option value="SalesManager">Sales Manager</option>
              <option value="RevenueManager">Revenue Manager</option>
              <option value="Manager">General Manager</option>
            </select>
          </div> -->
                </div>
                <button type="submit" class="btn-login">Sign In to System</button>
            </form>

            <p class="footer-note">
                Access is restricted to authorised hotel staff only.<br>
                All sessions are logged and audited.
            </p>
        </div>
    </div>

    <script>
        function validate(e) {
            const u = document.querySelector('[name=username]').value.trim();
            const p = document.querySelector('[name=password]').value;
            if (!u || !p) {
                e.preventDefault();
                document.getElementById('errMsg').style.display = 'block';
                document.getElementById('errMsg').textContent = 'Please fill in all fields.';
                return false;
            }
            return true;
        }
    </script>
</body>

</html>