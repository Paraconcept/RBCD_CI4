<style>
.news-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: 4px solid #84252B;
    border-radius: 6px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow .2s;
}
.news-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }
.news-card-img-wrap { display: block; overflow: hidden; }
.news-card-img { width: 100%; height: 190px; object-fit: contain; background: #f0f0f0; display: block; transition: transform .3s; }
.news-card-img-wrap:hover .news-card-img { transform: scale(1.20); }
.news-card-img-placeholder { height: 190px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 2.5rem; }
.news-card-body { padding: 20px 22px 22px; display: flex; flex-direction: column; flex: 1; }
.news-card-date { font-size: .78rem; color: #84252B; font-weight: 600; margin-bottom: 8px; }
.news-card-title { font-size: 1rem; font-weight: 700; margin: 0 0 10px; line-height: 1.4; }
.news-card-title a { color: #222; text-decoration: none; }
.news-card-title a:hover { color: #84252B; }
.news-card-excerpt { font-size: .87rem; color: #555; line-height: 1.55; flex: 1; margin-bottom: 16px; }
.news-card-link { font-size: .85rem; font-weight: 600; color: #84252B; text-decoration: none; align-self: flex-start; }
.news-card-link:hover { text-decoration: underline; }
</style>
