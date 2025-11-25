export interface Product {
    id?: number;
    name: string;
    description: string;
    authors?: string;
    publisher?: string;
    published_date?: string;
    page_count?: number;
    isbn?: string;
    google_book_id?: string;
    price: number;
    category: string;
    image_url: string;
    stock: number;
    is_active?: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface Review {
    id?: number;
    product_id: number;
    product_name?: string;
    name: string;
    email: string;
    rating: number;
    comment: string;
    is_approved?: boolean;
    created_at?: string;
}

export interface Admin {
    id?: number;
    username: string;
    email: string;
    password?: string;
    created_at?: string;
    last_login?: string;
}

export interface User {
    id?: number;
    username: string;
    email: string;
    password?: string;
    created_at?: string;
    last_login?: string;
    is_active?: boolean;
}

export interface AuthResponse {
    message: string;
    session_id?: string;
    admin?: Admin;
    user?: User;
}

export interface GoogleBook {
    id: string;
    volumeInfo: {
        title: string;
        authors?: string[];
        description?: string;
        imageLinks?: {
            thumbnail?: string;
            smallThumbnail?: string;
        };
        categories?: string[];
        publishedDate?: string;
        pageCount?: number;
        averageRating?: number;
        ratingsCount?: number;
        infoLink?: string;
    };
    saleInfo?: {
        listPrice?: {
            amount: number;
            currencyCode: string;
        };
    };
}

export interface BookSection {
    title: string;
    books: GoogleBook[];
    loading: boolean;
}
