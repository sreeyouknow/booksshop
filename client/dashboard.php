<?php include '../client/include/header.php'; ?>
<?php include '../client/include/sidebar.php';
?>
<style>
/* Styling for the first div (Welcome message and Profile icon) */
section > div:first-child {
    display: flex;
    gap:50px;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

section > div:first-child h1 {
    font-size: 2.8em;
    margin: 0;
    font-weight: 300;
    letter-spacing: 1px;
}
section > div:first-child h1 span {
    color:black;
    font-weight:700;
}

section > div:first-child span:last-child a { /* Targeting the profile icon link */
    font-size: 2.5em; /* Make icon larger */
    text-decoration: none;
    color: white;
    transition: transform 0.3s ease;
}

section > div:first-child span:last-child a:hover {
    transform: scale(1.1);
}

/* Styling for the divs containing "browse books" links */
section > div:not(:first-child) {
    margin: 15px 0; /* Space between the link buttons */
}

section > div a { /* General styling for all links within these divs */
    display: inline-block; /* Allows padding, margin, and text-align to work well */
    padding: 15px 35px;
    background-color: rgba(255, 255, 255, 0.15);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 1.2em;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

section > div a:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px); /* Slight lift on hover */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

section > div a:active {
    transform: translateY(-1px); /* Slight press effect */
}

/* If you want the "browse books" links to be full-width within their containers (up to the max-width of the section content) */

section > div:not(:first-child) a {
    display: block;
    width: 100%;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

</style>
<section>
    <div>
        <span><h1>Welcome |<span><?php echo $c_user_name; ?></span></h1></span>
        <span><a href="update_profile.php"> 👤</a></span>
    </div>
    <div>
        <a href="view-books.php" style="color:#1a2942;">Browse books</a>
    </div>
    <div>
        <a href= "book-request.php" style="color:#1a2942;">Request Book</a>
    </div>
    <div>
        <a href="messages.php" style="color:#1a2942;">Contact Agent</a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

