import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
    constructor(private router: Router) { }

    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        const isOurApi = req.url.startsWith(environment.apiUrl) || req.url.startsWith('/api');

        if (isOurApi) {
            const sessionId = localStorage.getItem('session_id');

            if (sessionId) {
                const clonedReq = req.clone({
                    headers: req.headers.set('Authorization', `Bearer ${sessionId}`)
                });

                return next.handle(clonedReq).pipe(
                    catchError((error: HttpErrorResponse) => {
                        // Если сессия недействительна, очищаем localStorage и перенаправляем на логин
                        if (error.status === 401) {
                            localStorage.removeItem('session_id');
                            localStorage.removeItem('user_type');
                            // Перенаправляем только если не на странице логина/регистрации
                            const currentUrl = this.router.url;
                            if (!currentUrl.includes('/login') && !currentUrl.includes('/register')) {
                                this.router.navigate(['/login']);
                            }
                        }
                        return throwError(() => error);
                    })
                );
            }
        }

        return next.handle(req);
    }
}
