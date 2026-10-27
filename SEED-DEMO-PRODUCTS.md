# Seed the PAXIG demo catalog

This CLI-only seeder creates up to 1,000 published WooCommerce simple products with deterministic `PAXIG-DEMO-` SKUs, stock, prices, product categories, and Gender/Sport/Color attributes. Each SKU receives its own unique shoe photo from Wikimedia Commons. The seeder only accepts CC0, public-domain, CC BY, or CC BY-SA images; it stores the artist, license, and source page in WordPress and displays that credit on product cards and product pages.

Run from the WordPress container. The first command is read-only and does not call Wikimedia; `--apply` needs network access and writes products, image attachments, and a reusable image manifest to WordPress. A descriptive `PAXIG_WIKIMEDIA_CONTACT` environment variable (email or project URL) can be supplied for the Wikimedia User-Agent; otherwise the site URL is used.

```sh
docker exec shoes-shop-preview-wordpress-1 php /var/www/html/wp-content/themes/paxig-shoes/scripts/seed-demo-products.php --dry-run
docker exec shoes-shop-preview-wordpress-1 php /var/www/html/wp-content/themes/paxig-shoes/scripts/seed-demo-products.php --apply
```

Use `--limit=100` for a smaller initial batch. Re-running with `--limit=1000` resumes by skipping existing seed SKUs. The script refuses to write on a production environment unless the site uses a local URL or `--allow-production` is passed explicitly. It does not delete or update existing products.

Image discovery is cached in the `paxig_demo_commons_manifest_v1` WordPress option so interrupted runs continue with the same image-to-SKU assignments. The image files are downloaded into the Media Library as the corresponding products are created. Wikimedia throttling responses are retried with backoff; the import can take a while for 1,000 images. If Commons cannot supply enough distinct reusable images, the script stops before creating products.
