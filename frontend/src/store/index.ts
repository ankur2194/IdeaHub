import { configureStore } from '@reduxjs/toolkit';
import authReducer from './authSlice';
import ideasReducer from './ideasSlice';
import categoriesReducer from './categoriesSlice';
import tagsReducer from './tagsSlice';

export const store = configureStore({
  reducer: {
    auth: authReducer,
    ideas: ideasReducer,
    categories: categoriesReducer,
    tags: tagsReducer,
  },
});

// Infer the `RootState` and `AppDispatch` types from the store itself
export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
