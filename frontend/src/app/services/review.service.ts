import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Review } from '../models/models';

@Injectable({
    providedIn: 'root'
})
export class ReviewService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    // Публичные методы
    getReviewsByProduct(productId: number): Observable<Review[]> {
        return this.http.get<Review[]>(`${this.apiUrl}/reviews.php?product_id=${productId}`);
    }

    createReview(review: Review): Observable<any> {
        return this.http.post(`${this.apiUrl}/reviews.php`, review);
    }

    getApprovedReviews(): Observable<Review[]> {
        return this.http.get<Review[]>(`${this.apiUrl}/reviews.php?approved=1`);
    }

    // Методы администратора
    getAllReviews(filters: any = {}): Observable<Review[]> {
        let params = new HttpParams();
        if (filters.is_approved !== undefined) params = params.set('is_approved', filters.is_approved);
        if (filters.product_id) params = params.set('product_id', filters.product_id);
        if (filters.rating) params = params.set('rating', filters.rating);

        return this.http.get<Review[]>(`${this.apiUrl}/admin/reviews.php`, { params });
    }

    updateReviewApproval(id: number, isApproved: boolean): Observable<any> {
        return this.http.put(`${this.apiUrl}/admin/reviews.php`, {
            id,
            is_approved: isApproved
        });
    }

    deleteReview(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/admin/reviews.php?id=${id}`);
    }
}
