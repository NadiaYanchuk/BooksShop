import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Product } from '../models/models';

@Injectable({
    providedIn: 'root'
})
export class ProductService {
    private apiUrl = environment.apiUrl;

    constructor(private http: HttpClient) { }

    // Публичные методы
    getProducts(): Observable<Product[]> {
        return this.http.get<Product[]>(`${this.apiUrl}/products.php`);
    }

    getProduct(id: number): Observable<Product> {
        return this.http.get<Product>(`${this.apiUrl}/products.php?id=${id}`);
    }

    // Получить продукты с фильтрами
    getProductsWithFilters(filters: {
        keyword?: string,
        category?: string,
        minPrice?: number | null,
        maxPrice?: number | null
    }): Observable<Product[]> {
        let params = new HttpParams();

        if (filters.keyword && filters.keyword.trim() !== '') {
            params = params.set('keyword', filters.keyword.trim());
        }
        if (filters.category && filters.category !== '') {
            params = params.set('category', filters.category);
        }
        if (filters.minPrice !== null && filters.minPrice !== undefined) {
            params = params.set('minPrice', filters.minPrice.toString());
        }
        if (filters.maxPrice !== null && filters.maxPrice !== undefined) {
            params = params.set('maxPrice', filters.maxPrice.toString());
        }

        return this.http.get<Product[]>(`${this.apiUrl}/products.php`, { params });
    }

    searchProducts(keyword: string, category: string = ''): Observable<Product[]> {
        let params = new HttpParams().set('search', keyword);
        if (category) {
            params = params.set('category', category);
        }
        return this.http.get<Product[]>(`${this.apiUrl}/products.php`, { params });
    }

    // Методы администратора
    getProductsAdmin(filters: any = {}): Observable<Product[]> {
        let params = new HttpParams();
        if (filters.category) params = params.set('category', filters.category);
        if (filters.is_active !== undefined) params = params.set('is_active', filters.is_active);
        if (filters.search) params = params.set('search', filters.search);

        return this.http.get<Product[]>(`${this.apiUrl}/admin/products.php`, { params });
    }

    getCategories(): Observable<string[]> {
        return this.http.get<string[]>(`${this.apiUrl}/admin/products.php?categories=1`);
    }

    createProduct(product: Product): Observable<any> {
        return this.http.post(`${this.apiUrl}/admin/products.php`, product);
    }

    updateProduct(product: Product): Observable<any> {
        return this.http.put(`${this.apiUrl}/admin/products.php`, product);
    }

    deleteProduct(id: number): Observable<any> {
        return this.http.delete(`${this.apiUrl}/admin/products.php?id=${id}`);
    }
}
