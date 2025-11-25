import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ProductService } from '../../../services/product.service';
import { Product } from '../../../models/models';

@Component({
    selector: 'app-admin-products',
    templateUrl: './admin-products.component.html',
    styleUrls: ['./admin-products.component.css']
})
export class AdminProductsComponent implements OnInit {
    products: Product[] = [];
    categories: string[] = [];
    productForm: FormGroup;
    filters = { category: '', is_active: '', search: '' };
    loading = false;
    showForm = false;
    editingProduct: Product | null = null;
    message = '';
    messageType = '';

    constructor(
        private fb: FormBuilder,
        private productService: ProductService
    ) {
        this.productForm = this.fb.group({
            name: ['', Validators.required],
            description: [''],
            price: [0, [Validators.required, Validators.min(0)]],
            category: ['', Validators.required],
            image_url: [''],
            stock: [0, [Validators.required, Validators.min(0)]],
            is_active: [true]
        });
    }

    ngOnInit(): void {
        this.loadProducts();
        this.loadCategories();
    }

    loadProducts(): void {
        this.loading = true;
        this.productService.getProductsAdmin(this.filters).subscribe({
            next: (data: Product[]) => {
                this.products = data;
                this.loading = false;
            },
            error: (err: any) => {
                console.error(err);
                this.loading = false;
            }
        });
    }

    loadCategories(): void {
        this.productService.getCategories().subscribe({
            next: (data: string[]) => {
                this.categories = data;
            },
            error: (err: any) => console.error(err)
        });
    }

    onFilter(): void {
        this.loadProducts();
    }

    onAdd(): void {
        this.showForm = true;
        this.editingProduct = null;
        this.productForm.reset({ is_active: true, stock: 0, price: 0 });
    }

    onEdit(product: Product): void {
        this.showForm = true;
        this.editingProduct = product;
        this.productForm.patchValue(product);
    }

    onSubmit(): void {
        if (this.productForm.valid) {
            const product: Product = this.productForm.value;

            if (this.editingProduct) {
                product.id = this.editingProduct.id;
                this.productService.updateProduct(product).subscribe({
                    next: () => {
                        this.showMessage('Продукт успешно обновлен', 'success');
                        this.loadProducts();
                        this.showForm = false;
                    },
                    error: (err: any) => {
                        this.showMessage('Ошибка при обновлении продукта', 'danger');
                        console.error(err);
                    }
                });
            } else {
                this.productService.createProduct(product).subscribe({
                    next: () => {
                        this.showMessage('Продукт успешно создан', 'success');
                        this.loadProducts();
                        this.showForm = false;
                    },
                    error: (err: any) => {
                        this.showMessage('Ошибка при создании продукта', 'danger');
                        console.error(err);
                    }
                });
            }
        }
    }

    onDelete(id: number | undefined): void {
        if (id && confirm('Вы уверены, что хотите удалить этот продукт?')) {
            this.productService.deleteProduct(id).subscribe({
                next: () => {
                    this.showMessage('Продукт удален', 'success');
                    this.loadProducts();
                },
                error: (err: any) => {
                    this.showMessage('Ошибка при удалении продукта', 'danger');
                    console.error(err);
                }
            });
        }
    }

    onCancel(): void {
        this.showForm = false;
        this.editingProduct = null;
        this.productForm.reset({ is_active: true, stock: 0, price: 0 });
    }

    showMessage(text: string, type: string): void {
        this.message = text;
        this.messageType = type;
        setTimeout(() => {
            this.message = '';
        }, 3000);
    }
}
