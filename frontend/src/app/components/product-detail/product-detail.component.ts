import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ProductService } from '../../services/product.service';
import { ReviewService } from '../../services/review.service';
import { Product, Review } from '../../models/models';

@Component({
    selector: 'app-product-detail',
    templateUrl: './product-detail.component.html',
    styleUrls: ['./product-detail.component.css']
})
export class ProductDetailComponent implements OnInit {
    product: Product | null = null;
    reviews: Review[] = [];
    reviewForm: FormGroup;
    loading = true;
    submitting = false;
    successMessage = '';
    errorMessage = '';

    constructor(
        private route: ActivatedRoute,
        private productService: ProductService,
        private reviewService: ReviewService,
        private fb: FormBuilder
    ) {
        this.reviewForm = this.fb.group({
            name: ['', Validators.required],
            email: ['', [Validators.required, Validators.email]],
            rating: [5, [Validators.required, Validators.min(1), Validators.max(5)]],
            comment: ['', Validators.required]
        });
    }

    ngOnInit(): void {
        const id = this.route.snapshot.paramMap.get('id');
        if (id) {
            this.loadProduct(+id);
            this.loadReviews(+id);
        }
    }

    loadProduct(id: number): void {
        this.productService.getProduct(id).subscribe({
            next: (data) => {
                this.product = data;
                this.loading = false;
            },
            error: (err) => {
                console.error(err);
                this.errorMessage = 'Ошибка загрузки продукта';
                this.loading = false;
            }
        });
    }

    loadReviews(productId: number): void {
        this.reviewService.getReviewsByProduct(productId).subscribe({
            next: (data) => {
                this.reviews = data;
            },
            error: (err) => console.error(err)
        });
    }

    onSubmitReview(): void {
        if (this.reviewForm.valid && this.product) {
            this.submitting = true;
            this.successMessage = '';
            this.errorMessage = '';

            const review: Review = {
                ...this.reviewForm.value,
                product_id: this.product.id
            };

            this.reviewService.createReview(review).subscribe({
                next: () => {
                    this.successMessage = 'Спасибо за ваш отзыв! Он будет опубликован после модерации.';
                    this.reviewForm.reset({ rating: 5 });
                    this.submitting = false;
                },
                error: (err) => {
                    console.error(err);
                    this.errorMessage = 'Ошибка при отправке отзыва. Проверьте правильность введенных данных.';
                    this.submitting = false;
                }
            });
        }
    }

    getRatingStars(rating: number): string {
        return '★'.repeat(rating) + '☆'.repeat(5 - rating);
    }
}
