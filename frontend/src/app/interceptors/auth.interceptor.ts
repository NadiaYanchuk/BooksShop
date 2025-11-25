import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
    intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
        const isOurApi = req.url.startsWith(environment.apiUrl) || req.url.startsWith('/api');

        if (isOurApi) {
            const sessionId = localStorage.getItem('session_id');

            if (sessionId) {
                const clonedReq = req.clone({
                    headers: req.headers.set('Authorization', `Bearer ${sessionId}`)
                });
                return next.handle(clonedReq);
            }
        }

        return next.handle(req);
    }
}
