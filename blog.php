<?php 

require 'db.php';

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
    include('header.php');}
    ?>

<!DOCTYPE html>
<html>

<style>

.blog-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}
.blog-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding: 15px;
    text-align: center;
}
.blog-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
}

    .categories-nav {
      background-color:rgb(243, 255, 252);
      padding: 10px;
      text-align: center;
      border-bottom: 2px solid #abe2db;
  }
  
  .categories-nav ul {
      list-style:none;
      padding: 20;
      margin: 30;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
  }
  
  .categories-nav ul li {
      margin: 20px 20px;
  }
  
  .categories-nav ul li a {
      text-decoration:none ;
      
      color:rgb(41, 120, 124);
      font-weight: bold;
      padding: 4px 10px;
      border-radius: 15px;
      transition: background 0.3s, color 0.3s;
  }
  
  .categories-nav ul li a:hover {
      background-color:rgb(238, 173, 130);
      color: rgb(46, 116, 112);
  }
  .categories-nav {
    overflow-x: auto;
    white-space: nowrap;
}   

.categories-nav ul {
    display: inline-flex;
}

.blog-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center; /* Center cards */
    gap: 20px; /* Space between cards */
}

.card-blog img {
    transition: transform 0.3s ease-in-out;
}
.card-blog:hover img {
    transform: scale(1.05);
}
.card-blog h3 { font-size: 20px; }
.card-blog p { font-size: 16px; }

.card-blog {
    width: 100%; /* Make it responsive */
    max-width: 350px; /* Set a limit on width */
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow effect */
    overflow: hidden; /* Ensure images fit inside */
    text-align: left;
    transition: transform 0.3s ease-in-out;
}

.card-blog:hover {
    transform: translateY(-5px); /* Slight hover effect */
}

/* Adjust the image inside the card */
.card-blog img {
    width: 100%;
    height: 250px;
    object-fit: cover; /* Ensure image covers space without distortion */
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

/* Blog card content (text) */
.card-blog .content {
    padding: 30 px;
}

.card-blog h3 {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.card-blog p {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
}

/* Read more button */
.card-blog .read-more {
    display: inline-block;
    margin-top: 10px;
    color: #f15a29;
    font-weight: bold;
    text-decoration: none;
}
.card-blog .read-more:hover {
    text-decoration: underline;
}
/*tag cloud*/
.tag-cloud {
  background: #f0f8ff;
  padding: 10px;
  border-radius: 10px;
  margin-top: 40px;
}
.tag-cloud a {
  display: inline-block;
  margin: 5px;
  padding: 4px 5px;
  background: #e0ffff;
  border-radius: 20px;
  color: #333;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.3s ease;
}
.tag-cloud a:hover {
  background:rgb(248, 167, 91);
  color: #000;
}
.card-glass {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 20px;
  padding: 20px;
  margin-bottom: 30px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px 0 rgba(0, 255, 255, 0.37);
  color: #fff;
  transition: transform 0.3s ease;
}
.card-glass:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 40px 0 rgba(79, 179, 179, 0.6);
}
.card-glass img {
  max-width: 100%;
  border-radius: 15px;
}
.card-glass h2 {
  margin-top: 15px;
  color:rgb(247, 172, 111);
}
.card-glass .category,
.card-glass .created_at {
  font-size: 0.9rem;
  color: #ccc;
}
  .category-description {
    line-height: 2.0; 
    margin-bottom: 60px; 
    margin-top: 30px;
    }
</style>
    

<?php

include 'db.php';
// Get category ID from URL
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 1;

// Fetch categories
$categories_query = "SELECT id, name FROM blog_categories";
$categories_result = $conn->query($categories_query);
$all_categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $all_categories[] = $row;
}
  
// Fetch category details
$category_query = "SELECT name,description FROM blog_categories WHERE id = $category_id";
$category_result = $conn->query($category_query);
if ($category_result && $category_result->num_rows > 0) {
  $category = $category_result->fetch_assoc();
} else {
  // Handle case where category is not found
  $category = ['name' => 'Uncategorized', 'description' => 'No description available.'];
}

// Fetch recent posts
$recent_query = "SELECT id,title,image FROM blog_posts ORDER BY created_at DESC LIMIT 5";
$recent_result = $conn->query($recent_query);

// Search functionality
$searchQuery = "";
if (isset($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
    $searchQuery = "WHERE title LIKE '%$searchTerm%' OR content LIKE '%$searchTerm%'";
}
// Get all tags from blog posts (simple tag cloud)
$tag_query = "SELECT tags FROM blog_posts LIMIT 9";
$tag_result = $conn->query($tag_query);
$all_tags = [];

while ($tag_row = $tag_result->fetch_assoc()) {
    $tags = explode(',', $tag_row['tags']);
    foreach ($tags as $tag) {
        $cleaned = trim($tag);
        if ($cleaned) {
            if (!isset($all_tags[$cleaned])) {
                $all_tags[$cleaned] = 1;
            } else {
                $all_tags[$cleaned]++;
            }
        }
    }
}
      // Pagination Setup
      $limit = 8;
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $offset = ($page - 1) * $limit;

      /* Filter by category if set
      $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
*/
      if ($category_id) {
        $stmt = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS blog_posts.*, blog_categories.name 
        AS category_name FROM blog_posts JOIN blog_categories
         ON blog_posts.category_id = blog_categories.id WHERE category_id = ? ORDER BY created_at DESC LIMIT ?, ?");
        $stmt->bind_param("iii", $category_id, $offset, $limit);
      } else {
        $stmt = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS blog_posts.*,
         blog_categories.name AS category_name FROM blog_posts JOIN blog_categories
          ON blog_posts.category_id = blog_categories.id ORDER BY created_at DESC LIMIT ?, ?");
        $stmt->bind_param("ii", $offset, $limit);
      }

      $stmt->execute();
      $posts = $stmt->get_result();

      // Total pages
      $total_result = $conn->query("SELECT FOUND_ROWS() as total")->fetch_assoc();
      $total_pages = ceil($total_result['total'] / $limit);
      ?>

  <div class="categories-nav">
      <ul>
        <?php foreach ($all_categories as $cat): ?>
          <li><a href="blog.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
        <?php endforeach; ?>
</ul>
  </div>

                                        <!-- blog page -->
  <div class="page-section">
      <div class="container">
      <div class="row">
      <div class="text-center">
            <h1 class="mb-3 text-primary"><?= htmlspecialchars($category['name']) ?></h1>
            <h5 class="text-muted category-description mb-5"><?= htmlspecialchars($category['description']) ?></h5>
      </div>

                                      <!-- Blog Posts Section -->
        <div class="col-lg-8"> 
                  <?php while ($row = mysqli_fetch_assoc($posts)) { ?>
                    <div class="card-glass">
                     <div class="card mb-4">
                       <div class="row no-gutters">
                          <div class="col-md-4">
                              <img src="<?php echo $row['image']; ?>" class="card-img" alt="<?php echo $row['title']; ?>">
                          </div>
                        <div class="col-md-8">
                          <div class="card-body">
                          <h5 class="card-title"><?php echo $row['title']; ?></h5>
                          <p class="card-text"><?php echo $row['short_desc']; ?></p>
                          <p class="card-text">
                    <small class="text-muted"><?php echo $row['category_name']; ?> |
                      <?php echo date('F j, Y', strtotime($row['created_at'])); ?></small>
                  </p>
                  <a href="blogdetails.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Read More</a>
                      </div></div></div></div></div>
                    <?php } ?>

                    
                   <!-- Pagination -->

                   <nav aria-label="Page navigation">
                      <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="blog.php?page=<?= $i ?><?= $category_id ? '&category_id=' . $category_id : '' ?>">
                              <?= $i ?>
                            </a>
                          </li>
                        <?php endfor; ?>
                      </ul>
                    </nav>
</div>

                        <!-- Sidebar -->
                        <div class="col-lg-4">
                          <div class="sidebar">

                            <!-- Search -->
                                  <div class="sidebar-block mb-4">
                                    <h3 class="sidebar-title">Search</h3>
                                    <form method="GET" action=""class="search-form">
                                      <div class="form-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search topics and articles..">

                                        <button type="submit" class="btn btn-outline-primary ml-2"><span class="icon mai-search"></span></button>
                                      </div>
                                    </form>
                                  </div>
                                             <!-- Recent Posts -->

                                  <div class="sidebar-block ">
                                    <h3 class="sidebar-title">Recent Blog</h3>
                                      <ul class="list-unstyled">
                                          <?php while ($recent = $recent_result->fetch_assoc()): ?>
                                              <li  class="d-flex mb-2">
                                                <a href="blogdetails.php?id=<?= $recent['id']; ?>"class="d-flex align-items-center">
                                                <img src="<?= htmlspecialchars($recent['image']) ?>" alt="Blog Image" style="width: 60px; height: 60px; object-fit: cover; margin-right: 10px;">
                                                <p class="mb-0"><?= htmlspecialchars($recent['title']) ?></p>
                                            </a></li>
                                          <?php endwhile; ?>
                                        </ul>
                                    </div>

                                                                    <!-- New Here Section -->
                                  <div class="sidebar-block">
                                    <h3 class="sidebar-title">New Here!</h3>
                                    <p style="font-size: large;">Join our healing journey</p>
                                    <a href="register.html">
                                      <button class="btn btn-primary">Sign Up</button>
                                    </a>  
                                  </div>
                                   
                                                           <!-- Tag Cloud -->
                                    <div class="sidebar-block">
                                    <h3 class="sidebar-title">Tag Cloud</h3>                          
                                    <div class="tag-cloud">
                                        <?php foreach ($all_tags as $tag => $count): ?>
                                            <a href="search.php?tag=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a>
                                        <?php endforeach; ?>
                                    </div></div>

                                                            <!-- Categories -->
                                                <div class="sidebar-block mb-4">
                                                    <h3 class="sidebar-title">Categories</h3>
                                                    <ul class="list-group">
                                                          <?php foreach ($all_categories as $cat): ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                              <a href="blog.php?category=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                                                            </li>
                                                          <?php endforeach; ?>
                                                        </ul>

                                                                                  <!--  end Categories -->

       </div> <!-- .sidebar -->
      </div> <!-- .col-lg-4 -->
    </div> <!-- .row -->
  </div> <!-- .container -->
</div> <!-- .page-section -->
        
<!-- Footer -->
<?php include "footer.php"?>

<!-- jQuery -->
<script src="assets/js/jquery.min.js"></script>

<!-- Bootstrap Core JS -->
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

<!-- Slick JS -->
<script src="assets/js/slick.js"></script>

<!-- Custom JS -->
<script src="assets/js/script.js"></script>


<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/php-email-form/valicreated_at
.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

<!-- Main JS File -->
<script src="assets/js/main.js"></script>
<script src="assets/js/counter.js"></script>



</body>

</html>