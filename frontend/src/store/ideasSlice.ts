import { createSlice, createAsyncThunk, type PayloadAction } from '@reduxjs/toolkit';
import { ideaService } from '../services/ideaService';
import type { CreateIdeaData, Idea, IdeaFilters, PaginatedResponse, UpdateIdeaData } from '../types';

interface IdeasState {
  ideas: Idea[];
  currentIdea: Idea | null;
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  } | null;
  filters: IdeaFilters;
  loading: boolean;
  error: string | null;
}

const initialState: IdeasState = {
  ideas: [],
  currentIdea: null,
  pagination: null,
  filters: {
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: 15,
    page: 1,
  },
  loading: false,
  error: null,
};

// Async thunks
export const fetchIdeas = createAsyncThunk(
  'ideas/fetchIdeas',
  async (filters: IdeaFilters | undefined, { rejectWithValue }) => {
    try {
      const response = await ideaService.getIdeas(filters);
      return response.data;
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to fetch ideas';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

export const fetchIdea = createAsyncThunk(
  'ideas/fetchIdea',
  async (id: number, { rejectWithValue }) => {
    try {
      const response = await ideaService.getIdea(id);
      return response.data;
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to fetch idea';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

export const createIdea = createAsyncThunk(
  'ideas/createIdea',
  async (data: CreateIdeaData | FormData, { rejectWithValue }) => {
    try {
      const response = await ideaService.createIdea(data);
      return response.data;
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to create idea';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

export const updateIdea = createAsyncThunk(
  'ideas/updateIdea',
  async ({ id, data }: { id: number; data: UpdateIdeaData }, { rejectWithValue }) => {
    try {
      const response = await ideaService.updateIdea(id, data);
      return response.data;
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to update idea';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

export const deleteIdea = createAsyncThunk(
  'ideas/deleteIdea',
  async (id: number, { rejectWithValue }) => {
    try {
      await ideaService.deleteIdea(id);
      return id;
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to delete idea';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

export const submitIdea = createAsyncThunk(
  'ideas/submitIdea',
  async (id: number, { rejectWithValue }) => {
    try {
      const response = await ideaService.submitIdea(id);
      return response.data;
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to submit idea';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

export const likeIdea = createAsyncThunk(
  'ideas/likeIdea',
  async (id: number, { rejectWithValue }) => {
    try {
      const response = await ideaService.likeIdea(id);
      return { id, ...response.data };
    } catch (error: unknown) {
      const errorMessage = error instanceof Error ? error.message : 'Failed to like idea';
      const apiError = error as { response?: { data?: { message?: string } } };
      return rejectWithValue(apiError.response?.data?.message || errorMessage);
    }
  }
);

// Slice
const ideasSlice = createSlice({
  name: 'ideas',
  initialState,
  reducers: {
    setFilters: (state, action: PayloadAction<IdeaFilters>) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearError: (state) => {
      state.error = null;
    },
    clearCurrentIdea: (state) => {
      state.currentIdea = null;
    },
  },
  extraReducers: (builder) => {
    // Fetch ideas
    builder
      .addCase(fetchIdeas.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchIdeas.fulfilled, (state, action: PayloadAction<PaginatedResponse<Idea>>) => {
        state.loading = false;
        state.ideas = action.payload.data;
        state.pagination = {
          current_page: action.payload.current_page,
          per_page: action.payload.per_page,
          total: action.payload.total,
          last_page: action.payload.last_page,
        };
      })
      .addCase(fetchIdeas.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Fetch single idea
    builder
      .addCase(fetchIdea.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchIdea.fulfilled, (state, action: PayloadAction<Idea>) => {
        state.loading = false;
        state.currentIdea = action.payload;
      })
      .addCase(fetchIdea.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Create idea
    builder
      .addCase(createIdea.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(createIdea.fulfilled, (state, action: PayloadAction<Idea>) => {
        state.loading = false;
        state.ideas.unshift(action.payload);
      })
      .addCase(createIdea.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Update idea
    builder
      .addCase(updateIdea.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(updateIdea.fulfilled, (state, action: PayloadAction<Idea>) => {
        state.loading = false;
        const index = state.ideas.findIndex((idea) => idea.id === action.payload.id);
        if (index !== -1) {
          state.ideas[index] = action.payload;
        }
        if (state.currentIdea?.id === action.payload.id) {
          state.currentIdea = action.payload;
        }
      })
      .addCase(updateIdea.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Delete idea
    builder
      .addCase(deleteIdea.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(deleteIdea.fulfilled, (state, action: PayloadAction<number>) => {
        state.loading = false;
        state.ideas = state.ideas.filter((idea) => idea.id !== action.payload);
      })
      .addCase(deleteIdea.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Submit idea
    builder
      .addCase(submitIdea.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(submitIdea.fulfilled, (state, action: PayloadAction<Idea>) => {
        state.loading = false;
        if (state.currentIdea?.id === action.payload.id) {
          state.currentIdea = action.payload;
        }
      })
      .addCase(submitIdea.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });

    // Like idea
    builder
      .addCase(likeIdea.pending, (state) => {
        // Optional: Add loading state for like action if needed
        state.error = null;
      })
      .addCase(likeIdea.fulfilled, (state, action) => {
        const { id, liked, likes_count } = action.payload;
        const idea = state.ideas.find((i) => i.id === id);
        if (idea) {
          idea.likes_count = likes_count;
          idea.liked = liked;
        }
        if (state.currentIdea?.id === id) {
          state.currentIdea.likes_count = likes_count;
          state.currentIdea.liked = liked;
        }
      })
      .addCase(likeIdea.rejected, (state, action) => {
        state.error = action.payload as string;
      });
  },
});

export const { setFilters, clearError, clearCurrentIdea } = ideasSlice.actions;
export default ideasSlice.reducer;
