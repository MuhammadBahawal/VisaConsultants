function getQueryParam(name) {
    const params = new URLSearchParams(location.search);
    return params.get(name);
}

async function loadBlogDetail() {
    const slug = getQueryParam('slug');
    const container = document.getElementById('blogDetail');
    if (!slug) {
        if (container) container.innerHTML = '<p style="text-align:center;padding:40px;">Invalid article.</p>';
        return;
    }

    try {
        const res = await fetch('./api/get-blog.php');
        const data = await res.json();
        const blogs = data.blogs || [];
        const blog = blogs.find(b => b.slug === decodeURIComponent(slug) || b.slug === slug);
        if (!blog) {
            container.innerHTML = '<p style="text-align:center;padding:40px;">Article not found.</p>';
            return;
        }

        document.title = blog.title + ' — A&M Visa Consultants';

        const imageHtml = blog.image_url ? `<div class="blog-detail-image"><img src="${escapeHtml(blog.image_url)}" alt="${escapeHtml(blog.title)}"></div>` : '';
        const date = blog.created_at ? new Date(blog.created_at).toLocaleDateString() : '';
        const category = blog.category ? `<small style="color:#0b6efd">${escapeHtml(blog.category)}</small>` : '';
        const contentHtml = blog.content ? blog.content : blog.short_description || '';

        container.innerHTML = `
            <div class="blog-detail-hero">
                <h1>${escapeHtml(blog.title)}</h1>
                <div class="blog-detail-meta">${category} <span style="margin-left:8px;color:#666">${date}</span></div>
                ${imageHtml}
            </div>
            <article class="blog-detail-content">${contentHtml}</article>
        `;
    } catch (err) {
        console.error(err);
        if (container) container.innerHTML = '<p style="text-align:center;padding:40px;">Unable to load article.</p>';
    }
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

document.addEventListener('DOMContentLoaded', loadBlogDetail);