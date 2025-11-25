import { Component, OnInit } from '@angular/core';
import { ReviewService } from '../../../services/review.service';
import { Review } from '../../../models/models';

@Component({
    selector: 'app-admin-reviews',
    templateUrl: './admin-reviews.component.html',
    styleUrls: ['./admin-reviews.component.css']
})
export class AdminReviewsComponent implements OnInit {
    reviews: Review[] = [];
    filters = { is_approved: '', product_id: '', rating: '' };
    loading = false;
    message = '';
    messageType = '';

    constructor(private reviewService: ReviewService) { }

    ngOnInit(): void {
        this.loadReviews();
    }

    loadReviews(): void {
        this.loading = true;
        this.reviewService.getAllReviews(this.filters).subscribe({
            next: (data: Review[]) => {
                this.reviews = data;
                this.loading = false;
            },
            error: (err: any) => {
                console.error(err);
                this.loading = false;
            }
        });
    }

    onFilter(): void {
        this.loadReviews();
    }

    onApprove(id: number | undefined, isApproved: boolean): void {
        if (id) {
            this.reviewService.updateReviewApproval(id, isApproved).subscribe({
                next: () => {
                    this.showMessage(isApproved ? 'Отзыв одобрен' : 'Одобрение отменено', 'success');
                    this.loadReviews();
                },
                error: (err: any) => {
                    this.showMessage('Ошибка при обновлении статуса', 'danger');
                    console.error(err);
                }
            });
        }
    }

    onDelete(id: number | undefined): void {
        if (id && confirm('Вы уверены, что хотите удалить этот отзыв?')) {
            this.reviewService.deleteReview(id).subscribe({
                next: () => {
                    this.showMessage('Отзыв удален', 'success');
                    this.loadReviews();
                },
                error: (err: any) => {
                    this.showMessage('Ошибка при удалении отзыва', 'danger');
                    console.error(err);
                }
            });
        }
    }

    showMessage(text: string, type: string): void {
        this.message = text;
        this.messageType = type;
        setTimeout(() => {
            this.message = '';
        }, 3000);
    }

    getRatingStars(rating: number): string {
        return '★'.repeat(rating) + '☆'.repeat(5 - rating);
    }
}
