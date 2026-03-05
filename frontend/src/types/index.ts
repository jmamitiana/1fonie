export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'company' | 'provider';
  phone?: string;
  avatar?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
  company?: Company;
  provider?: Provider;
}

export interface Company {
  id: number;
  user_id: number;
  company_name: string;
  company_address?: string;
  company_city?: string;
  company_country?: string;
  company_zipcode?: string;
  company_phone?: string;
  company_website?: string;
  company_tax_id?: string;
  company_latitude?: number;
  company_longitude?: number;
}

export interface Provider {
  id: number;
  user_id: number;
  business_name?: string;
  description?: string;
  specialty?: string;
  service_categories?: string[];
  service_areas?: string[];
  license_number?: string;
  license_expiry?: string;
  hourly_rate?: number;
  latitude?: number;
  longitude?: number;
  stripe_account_id?: string;
  is_verified: boolean;
  is_available: boolean;
  rating: number;
  total_reviews: number;
}

export interface Mission {
  id: number;
  company_id: number;
  provider_id?: number;
  title: string;
  description: string;
  category: MissionCategory;
  location_city: string;
  location_address?: string;
  location_country?: string;
  location_zipcode?: string;
  latitude?: number;
  longitude?: number;
  intervention_date: string;
  intervention_time?: string;
  price: number;
  platform_fee: number;
  status: MissionStatus;
  attachments?: string[];
  cancellation_reason?: string;
  assigned_at?: string;
  started_at?: string;
  completed_at?: string;
  created_at: string;
  updated_at: string;
  company?: Company;
  provider?: Provider;
  has_applied?: boolean;
}

export type MissionCategory =
  | 'it_support'
  | 'plumbing'
  | 'electrical'
  | 'network_installation'
  | 'hvac'
  | 'security'
  | 'maintenance'
  | 'construction'
  | 'other';

export type MissionStatus =
  | 'draft'
  | 'open'
  | 'in_review'
  | 'assigned'
  | 'in_progress'
  | 'completed'
  | 'cancelled'
  | 'disputed';

export interface Application {
  id: number;
  mission_id: number;
  provider_id: number;
  cover_letter?: string;
  proposed_price?: number;
  proposed_date?: string;
  notes?: string;
  status: 'pending' | 'accepted' | 'rejected' | 'withdrawn';
  reviewed_at?: string;
  created_at: string;
  provider?: Provider;
}

export interface Payment {
  id: number;
  mission_id: number;
  company_id: number;
  provider_id: number;
  stripe_payment_intent_id?: string;
  stripe_transfer_id?: string;
  amount: number;
  platform_fee: number;
  provider_amount: number;
  status: PaymentStatus;
  currency: string;
  description?: string;
  paid_at?: string;
  released_at?: string;
  created_at: string;
}

export type PaymentStatus =
  | 'pending'
  | 'processing'
  | 'held'
  | 'released'
  | 'refunded'
  | 'failed'
  | 'disputed';

export interface Review {
  id: number;
  mission_id: number;
  reviewer_id: number;
  reviewee_id: number;
  provider_id: number;
  rating: number;
  comment?: string;
  type: 'company_to_provider' | 'provider_to_company';
  created_at: string;
}

export interface Message {
  id: number;
  mission_id: number;
  sender_id: number;
  receiver_id: number;
  content: string;
  is_read: boolean;
  read_at?: string;
  created_at: string;
  sender?: User;
  receiver?: User;
}

export interface Notification {
  id: number;
  user_id: number;
  type: string;
  title: string;
  message: string;
  data?: Record<string, any>;
  is_read: boolean;
  read_at?: string;
  created_at: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export interface DashboardStats {
  total_missions?: number;
  open_missions?: number;
  in_progress_missions?: number;
  completed_missions?: number;
  total_spent?: number;
  available_missions?: number;
  assigned_missions?: number;
  total_earnings?: number;
}

export const MISSION_CATEGORIES: { value: MissionCategory; label: string }[] = [
  { value: 'it_support', label: 'IT Support' },
  { value: 'plumbing', label: 'Plumbing' },
  { value: 'electrical', label: 'Electrical' },
  { value: 'network_installation', label: 'Network Installation' },
  { value: 'hvac', label: 'HVAC' },
  { value: 'security', label: 'Security' },
  { value: 'maintenance', label: 'Maintenance' },
  { value: 'construction', label: 'Construction' },
  { value: 'other', label: 'Other' },
];

export const MISSION_STATUSES: { value: MissionStatus; label: string }[] = [
  { value: 'draft', label: 'Draft' },
  { value: 'open', label: 'Open' },
  { value: 'in_review', label: 'In Review' },
  { value: 'assigned', label: 'Assigned' },
  { value: 'in_progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'disputed', label: 'Disputed' },
];
