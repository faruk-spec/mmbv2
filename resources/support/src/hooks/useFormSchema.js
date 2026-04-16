import { useState, useCallback } from 'react';

/**
 * useFormSchema — fetch and cache a template schema from the API.
 * @param {string} apiBase – e.g. '/api/support'
 */
export function useFormSchema(apiBase) {
  const [loading, setLoading] = useState(false);
  const [error, setError]     = useState(null);
  const [schema, setSchema]   = useState(null);
  const [templateId, setTemplateId] = useState(null);

  const fetchSchema = useCallback(async (categoryId) => {
    setLoading(true);
    setError(null);
    try {
      const res  = await fetch(`${apiBase}/template?category_id=${categoryId}`);
      const json = await res.json();
      if (!res.ok || !json.ok) {
        setError(json.error ?? 'Failed to load template.');
        setSchema(null);
      } else {
        setSchema(json.data.schema);
        setTemplateId(json.data.template_id);
      }
    } catch (e) {
      setError('Network error: ' + e.message);
    } finally {
      setLoading(false);
    }
  }, [apiBase]);

  return { schema, templateId, loading, error, fetchSchema };
}
