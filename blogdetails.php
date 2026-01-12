<?php 
session_start(); // Start the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $role = isset($_SESSION['roll']) ? $_SESSION['roll'] : 'User';
    $profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'assets/img/random.png';
} else {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}
if ($role == 'doctor') {
    include('headerd.php');    
}
else {
    include('header.php');}?>
    
<style>
    
    .blog-container {
    width: 60%;
    margin: auto;
}

.article-content p {
    font-size: 1.2rem;
    line-height: 1.4;
    color: #444;
    text-align: justify;

}
.related-posts .card {
    transition: all 0.3s ease-in-out;
}

.related-posts .card:hover {
    transform: scale(1.03);
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
}
.blog-header {
        background-color: rgb(196, 253, 241);
        border-radius: 0 0 50px 50px;
        padding: 50px 0px;
       margin-bottom: 20px;
        text-align: center;

    }

    .blog-header h1 {
        font-size: 3.0rem;
        color:rgb(57, 58, 58);
        font-weight: bold;

    }

    .blog-header p {
        color: #333;
        font-weight: bold;
    }

    .blog-image {
      max-height: 400px;
        object-fit: contain;
        border-radius: 40px;
        width: 60%;
        margin: 20 auto;
        display: inline-flex	;
    }

    .related-posts h3 {
        color: #003366;
    }

    .related-posts .card {
        border-radius: 20px;
        background: #f7f9fa;
    }

    .related-posts .card-img-top {
        height: 150px;
        object-fit: cover;
        border-radius: 20px 20px 0 0;
    }

    .related-posts .card-title {
        font-size: 1.1rem;
        color: #003366;
    }

    .btn-outline-primary {
        border-color: #003366;
        color: #003366;
    }

    .btn-outline-primary:hover {
        background-color: #003366;
        color: #fff;
    }

    .btn-secondary {
        background-color: #003366;
        border-color: #003366;
    }

    .btn-secondary:hover {
        background-color: #002244;
        border-color: #002244;
    }
 </style>

<?php
include 'db.php'; 

if (isset($_GET['id'])) {
    $blog_id = intval($_GET['id']); // Secure the input

    $query = "SELECT blog_posts.*, blog_categories.name AS category 
              FROM blog_posts 
              JOIN blog_categories ON blog_posts.category_id = blog_categories.id 
              WHERE blog_posts.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $blog = $result->fetch_assoc();
    } else {
        echo "<p>Blog post not found.</p>";
        exit();
    }
} else {
    echo "<p>No blog post selected.</p>";
    exit();
}

// Fetch related posts
$related_query = "SELECT id, title, image FROM blog_posts 
                  WHERE category_id = {$blog['category_id']} AND id != $blog_id 
                  ORDER BY created_at DESC LIMIT 4";

$related_result = $conn->query($related_query);

?>

<!-- Blog Header (full width, centered content) -->
<div class="blog-header">
      <div style="max-width: 700px; margin: 0 auto; text-align: center;">
          <p style="color: #333;">
              <?= htmlspecialchars($blog['category']) ?>  |  
              <?= date('F j, Y', strtotime($blog['created_at'])) ?>
          </p>
          <h1><?= htmlspecialchars($blog['title']) ?></h1>
      </div>

</div>
<div class="text-center my-5">
              <img src="<?= $blog['image'] ?>" class="blog-image" alt="<?= $blog['title'] ?>">
</div> 


  <!-- Blog Main Content -->
<div class="container blog-container">
    <div class="article-content mb-5" >
        <p><?= nl2br($blog['content']) ?></p>
    </div>

    <!-- Related Posts -->
    <div class="related-posts">
            <h3 class="text-secondary mb-4">Related Posts</h3>
            <div class="row">
                <?php while($related = $related_result->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?= $related['image'] ?>" class="card-img-top" alt="<?= $related['title'] ?>" style="height: 150px; object-fit: cover; border-radius: 20px 20px 0 0;">
                            <div class="card-body text-center">
                                <h6 class="card-title"><?= htmlspecialchars($related['title']) ?></h6>
                                <a href="blogdetails.php?id=<?= $related['id'] ?>" class="btn btn-outline-primary btn-sm">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="text-center mt-7">
            <a href="blog.php" class="btn btn-secondary">← Back to Blog</a><br>
        </div>
</div>

<!-- Footer -->
<?php include "footer.php"?>
			<!-- /Footer -->
		   
	   </div>
	   <!-- /Main Wrapper -->
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Slick JS -->
		<script src="assets/js/slick.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/  30 Nov 2019 04:11:53 GMT -->
</html>