import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth.service';
import { environment } from '../../../environments/environment';

declare const grecaptcha: any;

@Component({
    selector: 'app-register',
    templateUrl: './register.component.html',
    styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {
    registerForm: FormGroup;
    loading = false;
    error = '';
    success = '';
    recaptchaSiteKey = environment.recaptchaSiteKey;
    recaptchaError = '';

    constructor(
        private fb: FormBuilder,
        private authService: AuthService,
        private router: Router
    ) {
        this.registerForm = this.fb.group({
            username: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(50), Validators.pattern(/^[a-zA-Z0-9_]+$/)]],
            email: ['', [Validators.required, Validators.email]],
            password: ['', [Validators.required, Validators.minLength(6)]],
            confirmPassword: ['', Validators.required]
        }, { validators: this.passwordMatchValidator });
    }

    ngOnInit(): void {
        this.loadRecaptcha();
    }

    loadRecaptcha(): void {
        const checkRecaptcha = setInterval(() => {
            if (typeof grecaptcha !== 'undefined' && grecaptcha.render) {
                clearInterval(checkRecaptcha);

                setTimeout(() => {
                    const recaptchaElement = document.getElementById('recaptcha-container');
                    if (recaptchaElement && !recaptchaElement.hasChildNodes()) {
                        grecaptcha.render('recaptcha-container', {
                            'sitekey': this.recaptchaSiteKey
                        });
                    }
                }, 100);
            }
        }, 100);
    }

    passwordMatchValidator(form: FormGroup) {
        const password = form.get('password');
        const confirmPassword = form.get('confirmPassword');

        if (password && confirmPassword && password.value !== confirmPassword.value) {
            confirmPassword.setErrors({ passwordMismatch: true });
            return { passwordMismatch: true };
        }

        return null;
    }

    onSubmit(): void {
        if (this.registerForm.valid) {
            this.loading = true;
            this.error = '';
            this.success = '';
            this.recaptchaError = '';

            const recaptchaResponse = grecaptcha.getResponse();

            if (!recaptchaResponse) {
                this.recaptchaError = 'Пожалуйста, подтвердите, что вы не робот';
                this.loading = false;
                return;
            }

            const { username, email, password } = this.registerForm.value;

            this.authService.register(username, email, password, recaptchaResponse).subscribe({
                next: (response) => {
                    this.success = 'Регистрация успешна! Перенаправление на страницу входа...';
                    this.loading = false;
                    setTimeout(() => {
                        this.router.navigate(['/login']);
                    }, 2000);
                },
                error: (err) => {
                    this.error = err.error?.message || 'Ошибка регистрации';
                    this.loading = false;
                    grecaptcha.reset();
                }
            });
        }
    }
}
