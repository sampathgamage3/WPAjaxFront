
// header.php
// initialize the submitUrl and PGNonce in header.php

<script type="text/javascript">
            var submitUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var PGNonce = '<?php echo wp_create_nonce('cloudian-ajax-nonce') ?>';
        </script>
        
 // ajax script  
 // ajax_script.js file 
 
 jQuery(document).ready(function(){

   $('#customer_industry_types').on('change', function() {

        var industry_filter = $(this).val().trim();
        var solution_filter = $('#customer_solutions_types').find(":selected").val().trim();


        jQuery('.customer_stories_pool').empty();
        jQuery('.loading_wrapper').fadeIn();
        
        jQuery.post(submitUrl, {
                  action : 'filter_posts',
                  pgnonce : PGNonce,
                  dataType: "html",
                  industry_cat:industry_filter,
                  solution_cat:solution_filter
              },
              function(responce) {  
                  jQuery('.loading_wrapper').hide();
                  jQuery('.customer_stories_pool').append(responce);
                  

              }
          );
    }); 
        
    
    // function.php
    // action call in function.php front call and admin side ajax call
    
     add_action('wp_ajax_filter_posts', 'ajax_filter_get_posts'); // admin side ajax action call
     add_action('wp_ajax_nopriv_filter_posts', 'ajax_filter_get_posts'); // font-side ajax action call
     
     
     // Script for getting posts
        function ajax_filter_get_posts( $taxonomy ) {

          // Verify nonce 
          if( !isset( $_POST['pgnonce'] ) || !wp_verify_nonce( $_POST['pgnonce'], 'cloudian-ajax-nonce' ) )
            die('Permission denied');

          $industry_cat = $_POST['industry_cat'];
          $solution_cat = $_POST['solution_cat'];

          $filter_solution_cat_type = array();
          if($solution_cat!="any"){
              $filter_solution_cat_type = array (
                  'taxonomy' => 'related_solutions_types',
                            'field'    => 'slug',
                            'terms'    => $solution_cat,
              );
          }
          $filter_industry_cat_type = array();
          if($industry_cat!="any"){
              $filter_industry_cat_type = array (
                  'taxonomy' => 'industry_types',
                            'field'    => 'slug',
                            'terms'    => $industry_cat,
              );
          }
          // WP Query
          $args = array(
            'post_type'      => 'successstories',
            'posts_per_page' => -1,
            "order" => 'DESC',
            'tax_query' => array(
                    (!empty($filter_industry_cat_type)?$filter_industry_cat_type:''),
                  (!empty($filter_solution_cat_type)?$filter_solution_cat_type:'')
            ) 
          );

          

          $query = new WP_Query( $args );
         
          if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();   
           $image_logo = get_field('success_stories_logo_image',get_the_ID());
           $download_link = get_field('download_pdf_file',get_the_ID());
           
           $download_link_html = ($download_link!=""?'<h5><a  href="'.$download_link.'" download>Read <span>Success Story</span> </a><span> &nbsp; <img src="'.get_template_directory_uri().'/images/nav_list_style.png" alt=""> </span></h5>':'');
            $result .= '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">

                    	<div class="row row_clr customer_row_box">
                        
                        	<img src="'.$image_logo.'" alt="" class="img-responsive"/>
                            
                            <div class="row row_clr customer_row_inner">
                                <h5>'.get_the_title().'</h5> 
                                '.get_the_content().' 
                                 
                            </div>    
                            '.$download_link_html.' 
                        	
                        </div>

                    </div>' ;
            
            

          endwhile; else:
            $result  = '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">

                    	<div class="row row_clr customer_row_box">
                        
                        <h2>No posts found</h2>
                        </div>
                        </div>';
             
          endif;

          //$result = json_encode($result);
          echo $result;

           die();
        }

     
     