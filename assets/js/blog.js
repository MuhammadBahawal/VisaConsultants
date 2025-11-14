// Load blogs from PHP backend
document.addEventListener('DOMContentLoaded', function() {
    loadBlogs();
});

let blogsCache = [];

async function loadBlogs() {
    try {
        const response = await fetch('./api/get-blog.php');
        const data = await response.json();
        blogsCache = (data.blogs || []);
        renderBlogs(blogsCache);
    } catch (error) {
        console.error('Error loading blogs:', error);
        const blogGrid = document.getElementById('blogGrid');
        if (blogGrid) blogGrid.innerHTML = '<p style="text-align:center;padding:40px;">Unable to load blogs.</p>';
    }
}

function renderBlogs(list) {
    const blogGrid = document.getElementById('blogGrid');
    if (!blogGrid) return;
    if (list.length === 0) {
        blogGrid.innerHTML = '<p style="text-align:center;padding:40px;">No blogs available yet.</p>';
        return;
    }

    blogGrid.innerHTML = list.map(blog => {
        const imageHtml = blog.image_url ? `<div class="blog-card-image"><img src="${blog.image_url}" alt="${escapeHtml(blog.title)}"><span class="blog-card-badge">${escapeHtml(blog.category || '')}</span></div>` : '';
        const desc = blog.short_description || (blog.content || '').substring(0, 120) + '...';
        const date = blog.created_at ? new Date(blog.created_at).toLocaleDateString() : '';
        const slug = encodeURIComponent(blog.slug);
        return `
            <a class="blog-card-link" href="./blog-detail.html?slug=${slug}">
                <article class="blog-card">
                    ${imageHtml}
                    <div class="blog-card-content">
                        <h3 class="blog-card-title">${escapeHtml(blog.title)}</h3>
                        <p class="blog-card-description">${escapeHtml(desc)}</p>
                        <div class="blog-card-meta"><span>${date}</span></div>
                    </div>
                </article>
            </a>
        `;
    }).join('');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    loadBlogs();

    const form = document.getElementById('blogSearchForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const q = (document.getElementById('searchInput')?.value || '').trim().toLowerCase();
            if (!q) {
                renderBlogs(blogsCache);
                return;
            }
            const filtered = blogsCache.filter(b => {
                return (b.title||'').toLowerCase().includes(q) ||
                       (b.short_description||'').toLowerCase().includes(q) ||
                       (b.content||'').toLowerCase().includes(q) ||
                       (b.category||'').toLowerCase().includes(q);
            });
            renderBlogs(filtered);
        });
    }
});