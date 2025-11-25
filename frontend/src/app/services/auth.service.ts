import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { AuthResponse, Admin } from '../models/models';

@Injectable({
    providedIn: 'root'
})
export class AuthService {
    private apiUrl = environment.apiUrl;
    private currentAdminSubject = new BehaviorSubject<Admin | null>(null);
    public currentAdmin$ = this.currentAdminSubject.asObservable();
    private userTypeSubject = new BehaviorSubject<string>('user');
    public userType$ = this.userTypeSubject.asObservable();

    constructor(private http: HttpClient) {
        const sessionId = this.getSessionId();
        const userType = this.getUserType();
        if (sessionId) {
            this.userTypeSubject.next(userType);
            this.validateSession().subscribe({
                error: () => this.logout()
            });
        }
    }

    login(username: string, password: string): Observable<AuthResponse> {
        return this.http.post<AuthResponse>(`${this.apiUrl}/auth.php`, {
            username,
            password
        }).pipe(
            tap(response => {
                if (response.session_id) {
                    localStorage.setItem('session_id', response.session_id);

                    const userType = response.admin ? 'admin' : 'user';
                    localStorage.setItem('user_type', userType);
                    this.userTypeSubject.next(userType);

                    if (response.admin) {
                        this.currentAdminSubject.next(response.admin);
                    } else if (response.user) {
                        this.currentAdminSubject.next(response.user as Admin);
                    }
                }
            })
        );
    }

    register(username: string, email: string, password: string, captcha: string): Observable<any> {
        return this.http.post(`${this.apiUrl}/register.php`, {
            username,
            email,
            password,
            recaptcha: captcha
        });
    }

    logout(): Observable<any> {
        const sessionId = this.getSessionId();
        if (sessionId) {
            return this.http.delete(`${this.apiUrl}/auth.php`).pipe(
                tap(() => {
                    localStorage.removeItem('session_id');
                    localStorage.removeItem('user_type');
                    this.currentAdminSubject.next(null);
                    this.userTypeSubject.next('user');
                })
            );
        }
        localStorage.removeItem('session_id');
        localStorage.removeItem('user_type');
        this.currentAdminSubject.next(null);
        this.userTypeSubject.next('user');
        return new Observable(observer => {
            observer.next(null);
            observer.complete();
        });
    }

    validateSession(): Observable<any> {
        return this.http.get(`${this.apiUrl}/auth.php`);
    }

    getSessionId(): string | null {
        return localStorage.getItem('session_id');
    }

    getUserType(): string {
        return localStorage.getItem('user_type') || 'user';
    }

    isAuthenticated(): boolean {
        return !!this.getSessionId();
    }

    isAdmin(): boolean {
        return this.isAuthenticated() && this.getUserType() === 'admin';
    }

    getCurrentAdmin(): Admin | null {
        return this.currentAdminSubject.value;
    }

    getAdminName(): string {
        const admin = this.getCurrentAdmin();
        return admin?.username || 'Пользователь';
    }
}
