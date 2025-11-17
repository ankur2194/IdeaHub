import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { formatDate, formatDateTime, truncateText, getInitials } from './formatters';

describe('formatDate', () => {
  let mockNow: Date;

  beforeEach(() => {
    // Mock current time to 2025-01-15 12:00:00
    mockNow = new Date('2025-01-15T12:00:00Z');
    vi.useFakeTimers();
    vi.setSystemTime(mockNow);
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('returns "just now" for dates less than a minute ago', () => {
    const date = new Date('2025-01-15T11:59:30Z').toISOString();
    expect(formatDate(date)).toBe('just now');
  });

  it('returns minutes ago for dates less than an hour ago', () => {
    const date1 = new Date('2025-01-15T11:59:00Z').toISOString();
    expect(formatDate(date1)).toBe('1 minute ago');

    const date2 = new Date('2025-01-15T11:30:00Z').toISOString();
    expect(formatDate(date2)).toBe('30 minutes ago');
  });

  it('returns hours ago for dates less than a day ago', () => {
    const date1 = new Date('2025-01-15T11:00:00Z').toISOString();
    expect(formatDate(date1)).toBe('1 hour ago');

    const date2 = new Date('2025-01-15T07:00:00Z').toISOString();
    expect(formatDate(date2)).toBe('5 hours ago');
  });

  it('returns days ago for dates less than a week ago', () => {
    const date1 = new Date('2025-01-14T12:00:00Z').toISOString();
    expect(formatDate(date1)).toBe('1 day ago');

    const date2 = new Date('2025-01-12T12:00:00Z').toISOString();
    expect(formatDate(date2)).toBe('3 days ago');
  });

  it('returns formatted date for dates older than a week', () => {
    const date = new Date('2025-01-01T12:00:00Z').toISOString();
    const formatted = formatDate(date);
    expect(formatted).toMatch(/Jan/);
    expect(formatted).toMatch(/1/);
    expect(formatted).toMatch(/2025/);
  });

  it('handles singular vs plural correctly', () => {
    const oneMinute = new Date('2025-01-15T11:59:00Z').toISOString();
    expect(formatDate(oneMinute)).toBe('1 minute ago');

    const twoMinutes = new Date('2025-01-15T11:58:00Z').toISOString();
    expect(formatDate(twoMinutes)).toBe('2 minutes ago');

    const oneHour = new Date('2025-01-15T11:00:00Z').toISOString();
    expect(formatDate(oneHour)).toBe('1 hour ago');

    const twoHours = new Date('2025-01-15T10:00:00Z').toISOString();
    expect(formatDate(twoHours)).toBe('2 hours ago');

    const oneDay = new Date('2025-01-14T12:00:00Z').toISOString();
    expect(formatDate(oneDay)).toBe('1 day ago');

    const twoDays = new Date('2025-01-13T12:00:00Z').toISOString();
    expect(formatDate(twoDays)).toBe('2 days ago');
  });
});

describe('formatDateTime', () => {
  it('formats date with time correctly', () => {
    const date = new Date('2025-01-15T14:30:00Z').toISOString();
    const formatted = formatDateTime(date);

    expect(formatted).toMatch(/Jan/);
    expect(formatted).toMatch(/15/);
    expect(formatted).toMatch(/2025/);
    expect(formatted).toMatch(/:/); // Contains time separator
  });

  it('includes hours and minutes', () => {
    const date = new Date('2025-06-20T09:45:00Z').toISOString();
    const formatted = formatDateTime(date);

    // Check that it contains time-related patterns
    expect(formatted).toMatch(/\d{1,2}:\d{2}/); // Time format HH:MM or H:MM
  });
});

describe('truncateText', () => {
  it('returns original text if shorter than max length', () => {
    const text = 'Short text';
    expect(truncateText(text, 20)).toBe('Short text');
  });

  it('returns original text if equal to max length', () => {
    const text = 'Exactly ten';
    expect(truncateText(text, 11)).toBe('Exactly ten');
  });

  it('truncates text and adds ellipsis if longer than max length', () => {
    const text = 'This is a very long text that needs to be truncated';
    const result = truncateText(text, 20);

    expect(result.length).toBeLessThanOrEqual(23); // 20 + '...'
    expect(result).toMatch(/\.{3}$/); // Ends with ...
    expect(result).toContain('This is a very');
  });

  it('trims whitespace before adding ellipsis', () => {
    const text = 'This is some text with spaces';
    const result = truncateText(text, 13);

    // Should not have space before ellipsis
    expect(result).not.toMatch(/ \.{3}$/);
    expect(result).toMatch(/\.{3}$/);
  });

  it('handles empty string', () => {
    expect(truncateText('', 10)).toBe('');
  });

  it('handles max length of 0', () => {
    const text = 'Some text';
    expect(truncateText(text, 0)).toBe('...');
  });
});

describe('getInitials', () => {
  it('returns first two letters for single name', () => {
    expect(getInitials('Alice')).toBe('AL');
    expect(getInitials('Bob')).toBe('BO');
  });

  it('returns first letter of first and last name for full name', () => {
    expect(getInitials('John Doe')).toBe('JD');
    expect(getInitials('Alice Smith')).toBe('AS');
  });

  it('uses first and last name for multiple names', () => {
    expect(getInitials('John Michael Doe')).toBe('JD');
    expect(getInitials('Mary Jane Watson Parker')).toBe('MP');
  });

  it('returns uppercase initials', () => {
    expect(getInitials('alice smith')).toBe('AS');
    expect(getInitials('john doe')).toBe('JD');
  });

  it('handles names with extra spaces', () => {
    expect(getInitials('  John   Doe  ')).toBe('JD');
    expect(getInitials('Alice    Smith')).toBe('AS');
  });

  it('handles single letter name', () => {
    expect(getInitials('A')).toBe('A');
  });

  it('handles two letter name', () => {
    expect(getInitials('AB')).toBe('AB');
  });

  it('handles unicode characters', () => {
    expect(getInitials('José García')).toBe('JG');
    expect(getInitials('François Müller')).toBe('FM');
  });
});
