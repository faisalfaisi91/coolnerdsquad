<?php

/**
 * @ file
 * Template Name: Cool Support
 */
get_header();
$slug = $_GET['p'];
$product_obj = new WC_Product(get_page_by_path($slug, OBJECT, 'product')->ID);
$product_id = $product_obj->get_ID();
// echo '<pre>';
// print_r($product_obj);
// echo '</pre>';
$services = get_field("services_include", $product_id);
$faqs = get_field('faqs', $product_id);
?>
<div class="container-fluid mt-5 mb-5">
    <div class="row">
        <div class="col-md-8">
            <img class="img-thumbnail w-100 d-block" src="<?php echo wp_get_attachment_url($product_obj->get_image_id()); ?>" alt="Slide Image" loading="lazy">
        </div>
        <div class="col-md-4">
            <h3><strong><?php echo $product_obj->get_name(); ?></strong></h3>
            <div class="price">
                <span>Starting at </span>
                <span><strong><?php echo get_woocommerce_currency_symbol() . $product_obj->get_price(); ?></span>
            </div>
            <div class="d-flex flex-row">
                <div class="icons mr-2">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                </div>
                <span>564</span>
            </div>
            <div class="mt-3">
                <a href="<?php echo get_permalink($product_id); ?>" class="btn btn-success">BOOK NOW</a>
            </div>
        </div>
    </div>
    <div class="clearfix pt-5"></div>
    <div class="row">
        <p><?php echo $product_obj->get_description(); ?></p>
    </div>
    <div class="row">
        <h3 class="text-left"><strong>Services Inculde:</strong></h3>
    </div>
    <div class="row">
        <div class="col-md-5">
            <ul>
                <?php
                foreach ($services as $service) {
                    if ($service['service_left']) { ?>
                        <li><?php echo $service['service_left'] ?></li>
                <?php }
                }
                ?>
            </ul>
        </div>
        <div class="col-md-5">
            <ul>
                <?php
                foreach ($services as $service) {
                    if ($service['service_right']) { ?>
                        <li><?php echo $service['service_right'] ?></li>
                <?php }
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="clearfix"></div>
    <hr class="container-full">
    <div class="mb-5"></div>
    <div class="row">
        <div class="col-lg-12 mx-auto">
            <h3>Frequently Asked Questions</h3>
            <!-- Accordion -->
            <div id="accordionExample" class="accordion shadow">
                <!-- Accordion item 1 -->
                <?php foreach ($faqs as $key => $faq) { ?>
                    <div class="card">
                        <div id="heading<?php echo $key ?>" class="card-header bg-white shadow-sm border-0">
                            <h6 class="mb-0 font-weight-bold"><a href="#" data-toggle="collapse" data-target="#collapse<?php echo $key ?>" aria-expanded="true" aria-controls="collapse<?php echo $key ?>" class="d-block position-relative text-dark text-uppercase collapsible-link py-2"><?php echo $faq['question'] ?></a></h6>
                        </div>
                        <div id="collapse<?php echo $key ?>" aria-labelledby="heading<?php echo $key ?>" data-parent="#accordionExample" class="collapse">
                            <div class="card-body p-5">
                                <?php echo $faq['answer']; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();
