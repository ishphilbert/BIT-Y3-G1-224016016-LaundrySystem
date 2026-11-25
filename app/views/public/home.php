<section class="hero">
    <div class="container hero-grid">
        <div>
            <h1>Fresh, clean laundry picked up from your door</h1>
            <p class="lead">Schedule a pickup online, choose your items, and track your laundry until it is back at your door.</p>
            <div class="btn-row">
                <a class="btn btn-accent" href="<?= e(url('register.php')) ?>">Schedule a Pickup</a>
                <a class="btn btn-light" href="<?= e(url('services.php')) ?>">View Services &amp; Prices</a>
            </div>
        </div>
        <img src="<?= e(url('assets/images/hero.svg')) ?>" alt="Illustration of a washing machine with bubbles" class="hero-img">
    </div>
</section>

<section class="container section">
    <h2 class="center">Our laundry services</h2>
    <div class="grid grid-4">
        <div class="card feature"><span class="icon">&#128085;</span><h3>Wash &amp; Fold</h3><p>Everyday clothes washed, dried and neatly folded.</p></div>
        <div class="card feature"><span class="icon">&#129509;</span><h3>Dry Cleaning</h3><p>Careful cleaning for suits, jackets and delicate fabrics.</p></div>
        <div class="card feature"><span class="icon">&#128716;</span><h3>Bedding</h3><p>Bedsheets, blankets and towels fresh and fluffy.</p></div>
        <div class="card feature"><span class="icon">&#128666;</span><h3>Pickup &amp; Delivery</h3><p>We collect and return your laundry on the dates you choose.</p></div>
    </div>
</section>

<section class="section alt">
    <div class="container">
        <h2 class="center">How it works</h2>
        <div class="grid grid-4">
            <div class="step"><span class="step-no">1</span><h3>Register</h3><p>Create a free customer account.</p></div>
            <div class="step"><span class="step-no">2</span><h3>Schedule</h3><p>Pick items, quantities and dates.</p></div>
            <div class="step"><span class="step-no">3</span><h3>We collect &amp; clean</h3><p>Our staff pick up and process your laundry.</p></div>
            <div class="step"><span class="step-no">4</span><h3>Track &amp; receive</h3><p>Follow progress online until delivery.</p></div>
        </div>
    </div>
</section>

<section class="container section">
    <h2 class="center">Why choose us</h2>
    <div class="grid grid-3">
        <div class="card"><h3>Convenient</h3><p>Book in minutes from your phone or computer.</p></div>
        <div class="card"><h3>Transparent prices</h3><p>See every item price and your total before you submit.</p></div>
        <div class="card"><h3>Always informed</h3><p>Track every stage from pickup to delivery.</p></div>
    </div>
</section>

<section class="section cta">
    <div class="container center">
        <h2>Ready for fresh laundry?</h2>
        <p>Questions? Call +250 788 000 000 or email hello@freshfold.test</p>
        <a class="btn btn-accent" href="<?= e(url('register.php')) ?>">Get started</a>
    </div>
</section>
