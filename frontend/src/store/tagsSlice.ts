import { createSlice, createAsyncThunk, type PayloadAction } from '@reduxjs/toolkit';
import { tagService } from '../services/tagService';
import type { Tag } from '../types';

interface TagsState {
  tags: Tag[];
  loading: boolean;
  error: string | null;
}

const initialState: TagsState = {
  tags: [],
  loading: false,
  error: null,
};

// Async thunks
export const fetchTags = createAsyncThunk(
  'tags/fetchTags',
  async (_, { rejectWithValue }) => {
    try {
      const response = await tagService.getTags();
      return response.data;
    } catch (error: any) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch tags');
    }
  }
);

// Slice
const tagsSlice = createSlice({
  name: 'tags',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchTags.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTags.fulfilled, (state, action: PayloadAction<Tag[]>) => {
        state.loading = false;
        state.tags = action.payload;
      })
      .addCase(fetchTags.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload as string;
      });
  },
});

export const { clearError } = tagsSlice.actions;
export default tagsSlice.reducer;
