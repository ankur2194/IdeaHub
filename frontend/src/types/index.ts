// User types
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'department_head' | 'team_lead' | 'user';
  department: string | null;
  job_title: string | null;
  bio: string | null;
  avatar: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

// Category types
export interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  color: string;
  icon: string;
  is_active: boolean;
  ideas_count?: number;
  created_at: string;
  updated_at: string;
}

// Tag types
export interface Tag {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  color: string;
  usage_count: number;
  created_at: string;
  updated_at: string;
}

// Idea types
export type IdeaStatus = 'draft' | 'submitted' | 'under_review' | 'approved' | 'rejected' | 'implemented' | 'archived';

export interface Idea {
  id: number;
  title: string;
  description: string;
  user_id: number;
  category_id: number | null;
  status: IdeaStatus;
  is_anonymous: boolean;
  likes_count: number;
  comments_count: number;
  views_count: number;
  attachments: string[] | null;
  submitted_at: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  implemented_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;

  // Relations
  user?: User;
  category?: Category;
  tags?: Tag[];
  comments?: Comment[];
  approvals?: Approval[];
  approvals_count?: number;
}

// Comment types
export interface Comment {
  id: number;
  idea_id: number;
  user_id: number;
  parent_id: number | null;
  content: string;
  likes_count: number;
  is_edited: boolean;
  created_at: string;
  updated_at: string;

  // Relations
  user?: User;
  idea?: Idea;
  replies?: Comment[];
  replies_count?: number;
}

// Approval types
export type ApprovalStatus = 'pending' | 'approved' | 'rejected';

export interface Approval {
  id: number;
  idea_id: number;
  approver_id: number;
  status: ApprovalStatus;
  level: number;
  comments: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  created_at: string;
  updated_at: string;

  // Relations
  idea?: Idea;
  approver?: User;
}

// API Response types
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number;
  to: number;
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

// Form types
export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  department?: string;
  job_title?: string;
}

export interface CreateIdeaData {
  title: string;
  description: string;
  category_id?: number;
  is_anonymous?: boolean;
  tags?: number[];
}

export interface UpdateIdeaData extends Partial<CreateIdeaData> {
  status?: IdeaStatus;
}

export interface CreateCommentData {
  idea_id: number;
  content: string;
  parent_id?: number;
}

// Filter types
export interface IdeaFilters {
  status?: IdeaStatus;
  category_id?: number;
  search?: string;
  sort_by?: 'created_at' | 'likes_count' | 'comments_count' | 'views_count';
  sort_order?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
}
