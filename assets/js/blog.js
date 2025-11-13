// Load blogs from PHP backend
document.addEventListener('DOMContentLoaded', function() {
    loadBlogs();
});

async function loadBlogs() {
    try {
        // Create a simple blog loading from database
        const response = await fetch('./api/get-blogs.php');
        const data = await response.json();
        
        const blogGrid = document.getElementById('blogGrid');
        
        if (data.blogs && data.blogs.length > 0) {
            blogGrid.innerHTML = data.blogs.map(blog => `
                <div class="blog-card">
                    ${blog.image_url ? `
                        <div class="blog-card-image">
                            <img src="${blog.image_url}" alt="${blog.title}">
                            <span class="blog-card-badge">${blog.category}</span>
                        </div>
                    ` : ''}
                    <div class="blog-card-content">
                        <h3 class="blog-card-title">${blog.title}</h3>
                        <p class="blog-card-description">${blog.short_description || blog.content.substring(0, 100)}...</p>
                        <div class="blog-card-meta">
                            <span class="blog-card-date">${new Date(blog.created_at).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</span>
                            <span class="blog-card-comments">No Comments</span>
                        </div>
                        <a href="blog-single.php?slug=${blog.slug}" class="blog-card-link">READ MORE →</a>
                    </div>
                </div>
            `).join('');
        } else {
            blogGrid.innerHTML = '<p style="text-align: center; padding: 40px;">No blogs available yet.</p>';
        }
    } catch (error) {
        console.error('Error loading blogs:', error);
    }
}

// Search functionality
document.getElementById('blogSearchForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    // Implement search logic
    console.log('Searching for:', searchTerm);
});