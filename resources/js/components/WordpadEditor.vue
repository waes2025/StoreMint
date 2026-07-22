<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount, onMounted } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import { TextStyle } from '@tiptap/extension-text-style';
import { FontFamily } from '@tiptap/extension-font-family';
import Superscript from '@tiptap/extension-superscript';
import Subscript from '@tiptap/extension-subscript';
import { Color } from '@tiptap/extension-color';
import Highlight from '@tiptap/extension-highlight';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';
import { Youtube } from '@tiptap/extension-youtube';
import { Extension } from '@tiptap/core';
import {
    Undo,
    Redo,
    AlignLeft,
    AlignCenter,
    AlignRight,
    AlignJustify,
    List,
    ListOrdered,
    Indent,
    Outdent,
    Link as LinkIcon,
    Image as ImageIcon,
    MoreHorizontal,
    ChevronDown,
    ChevronRight,
    Superscript as SuperscriptIcon,
    Subscript as SubscriptIcon,
    Code,
    Play,
    Stamp,
    Table as TableIcon,
    Minus,
    SeparatorHorizontal,
    Space,
    Bookmark,
    Clock,
    FileText,
    X,
    Trash2,
    Grid,
    Eye,
    Maximize,
    Minimize,
    Check,
    Pilcrow,
    Scissors,
    Copy,
    Clipboard,
    ClipboardType,
    BoxSelect,
    Search,
    File,
    Printer,
    Highlighter,
    Slash,
    Palette,
    Type,
    Heading,
    ArrowUpDown,
    Strikethrough,
    Bold,
    Italic,
    Underline as UnderlineIcon,
    Baseline,
    RemoveFormatting,
    SquareX,
    Settings2,
    Rows,
    Columns,
} from '@lucide/vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

// Custom FontSize Extension for Tiptap
const FontSize = Extension.create({
    name: 'fontSize',
    addOptions() {
        return {
            types: ['textStyle'],
        };
    },
    addGlobalAttributes() {
        return [
            {
                types: this.options.types,
                attributes: {
                    fontSize: {
                        default: null,
                        parseHTML: element => element.style.fontSize?.replace(/['"]+/g, ''),
                        renderHTML: attributes => {
                            if (!attributes.fontSize) {
                                return {};
                            }
                            return {
                                style: `font-size: ${attributes.fontSize}`,
                            };
                        },
                    },
                },
            },
        ];
    },
    addCommands() {
        return {
            setFontSize: (fontSize: string) => ({ chain }: any) => {
                return chain()
                    .setMark('textStyle', { fontSize })
                    .run();
            },
            unsetFontSize: () => ({ chain }: any) => {
                return chain()
                    .setMark('textStyle', { fontSize: null })
                    .removeEmptyTextStyle()
                    .run();
            },
        };
    },
});

const isCodeView = ref(false);
const showWordCountModal = ref(false);
const showPreviewModal = ref(false);
const showFindReplaceModal = ref(false);
const showTablePropsModal = ref(false);
const showCellPropsModal = ref(false);
const showRowPropsModal = ref(false);
const isFullscreen = ref(false);
const showVisualAids = ref(true);
const showInvisibleChars = ref(false);
const showBlocks = ref(false);
const selectedLineHeight = ref('1.15');

// Interactive 10x10 Table Grid state
const hoverGridRows = ref(0);
const hoverGridCols = ref(0);

const findText = ref('');
const replaceText = ref('');

const textColorInput = ref<HTMLInputElement | null>(null);
const bgColorInput = ref<HTMLInputElement | null>(null);

const rawHtml = ref(props.modelValue || '');
const activeMenu = ref<string | null>(null);
const activeFormatSubmenu = ref<string | null>(null);
const activeInsertSubmenu = ref<string | null>(null);
const activeTableSubmenu = ref<string | null>(null);

const editor = useEditor({
    content: props.modelValue || '',
    extensions: [
        StarterKit,
        Underline,
        TextStyle,
        FontFamily,
        FontSize,
        Superscript,
        Subscript,
        Color,
        Highlight.configure({
            multicolor: true,
        }),
        Table.configure({
            resizable: true,
            HTMLAttributes: {
                class: 'border-collapse border border-neutral-300 w-full my-4 text-xs',
            },
        }),
        TableRow,
        TableCell.configure({
            HTMLAttributes: {
                class: 'border border-neutral-300 p-2 dark:border-neutral-700',
            },
        }),
        TableHeader.configure({
            HTMLAttributes: {
                class: 'border border-neutral-300 p-2 font-bold bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-800',
            },
        }),
        Youtube.configure({
            inline: false,
            HTMLAttributes: {
                class: 'w-full aspect-video rounded-xl my-4 shadow-sm',
            },
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-blue-600 underline dark:text-blue-400',
            },
        }),
        Image.configure({
            HTMLAttributes: {
                class: 'max-w-full rounded-lg my-3',
            },
        }),
    ],
    onUpdate: () => {
        if (!editor.value) return;
        const html = editor.value.getHTML();
        rawHtml.value = html;
        emit('update:modelValue', html);
    },
});

watch(
    () => props.modelValue,
    (val) => {
        rawHtml.value = val || '';
        if (editor.value && editor.value.getHTML() !== val) {
            editor.value.commands.setContent(val || '', false);
        }
    }
);

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
    }
    window.removeEventListener('keydown', handleKeydown);
});

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey && e.shiftKey && (e.key === 'F' || e.key === 'f')) {
        e.preventDefault();
        toggleFullscreen();
    } else if (e.ctrlKey && (e.key === 'f' || e.key === 'F')) {
        e.preventDefault();
        openFindReplaceModal();
    }
};

// TinyMCE Exact Swatch Grid Colors
const tinymceColorSwatches = [
    '#D5E8D4', '#FFF2CC', '#F8CECC', '#E1D5E7', '#DAE8FC',
    '#22C55E', '#EAB308', '#EF4444', '#A855F7', '#3B82F6',
    '#0F766E', '#D97706', '#B91C1C', '#7E22CE', '#1D4ED8',
    '#F3F4F6', '#E5E7EB', '#9CA3AF', '#4B5563', '#1F2937',
];

// Rich Special Characters Grid (Including Bangladeshi Taka Symbol ৳)
const specialCharacters = [
    '৳', '©', '®', '™', '€', '£', '¥', '¢',
    '§', '¶', '•', '†', '‡', '°', '±', '÷',
    '×', 'µ', 'π', 'Ω', '∞', '≈', '≠', '≤',
    '≥', '√', '∆', '∑', 'α', 'β', 'γ', '→',
];

// Word Count statistics
const wordCount = computed(() => {
    const text = rawHtml.value.replace(/<[^>]*>/g, ' ').trim();
    if (!text) return 0;
    return text.split(/\s+/).filter(Boolean).length;
});

const charCount = computed(() => {
    return rawHtml.value.replace(/<[^>]*>/g, '').length;
});

const charCountNoSpaces = computed(() => {
    return rawHtml.value.replace(/<[^>]*>/g, '').replace(/\s+/g, '').length;
});

const paragraphCount = computed(() => {
    const text = rawHtml.value.replace(/<[^>]*>/g, '\n').trim();
    if (!text) return 0;
    return text.split(/\n+/).filter(Boolean).length;
});

// Active DOM Tag path
const activeTagPath = computed(() => {
    if (!editor.value) return 'P';
    if (editor.value.isActive('heading', { level: 1 })) return 'H1';
    if (editor.value.isActive('heading', { level: 2 })) return 'H2';
    if (editor.value.isActive('heading', { level: 3 })) return 'H3';
    if (editor.value.isActive('blockquote')) return 'BLOCKQUOTE';
    if (editor.value.isActive('bulletList')) return 'UL > LI';
    if (editor.value.isActive('orderedList')) return 'OL > LI';
    return 'P';
});

const handleHeadingChange = (e: Event) => {
    const val = (e.target as HTMLSelectElement).value;
    if (!editor.value) return;
    if (val === 'p') {
        editor.value.chain().focus().setParagraph().run();
    } else if (val === 'h1') {
        editor.value.chain().focus().toggleHeading({ level: 1 }).run();
    } else if (val === 'h2') {
        editor.value.chain().focus().toggleHeading({ level: 2 }).run();
    } else if (val === 'h3') {
        editor.value.chain().focus().toggleHeading({ level: 3 }).run();
    }
};

const handleFontFamilyChange = (e: Event) => {
    const val = (e.target as HTMLSelectElement).value;
    if (!editor.value) return;
    if (val === 'default') {
        editor.value.chain().focus().unsetFontFamily().run();
    } else {
        editor.value.chain().focus().setFontFamily(val).run();
    }
};

const handleFontSizeChange = (e: Event) => {
    const val = (e.target as HTMLSelectElement).value;
    if (!editor.value) return;
    if (val === 'default') {
        (editor.value.chain().focus() as any).unsetFontSize().run();
    } else {
        (editor.value.chain().focus() as any).setFontSize(val).run();
    }
};

const setTextColor = (color: string) => {
    if (!editor.value) return;
    if (!color) {
        editor.value.chain().focus().unsetColor().run();
    } else {
        editor.value.chain().focus().setColor(color).run();
    }
    activeMenu.value = null;
    activeFormatSubmenu.value = null;
};

const setBgColor = (color: string) => {
    if (!editor.value) return;
    if (!color) {
        editor.value.chain().focus().unsetHighlight().run();
    } else {
        editor.value.chain().focus().toggleHighlight({ color }).run();
    }
    activeMenu.value = null;
    activeFormatSubmenu.value = null;
};

const setLink = () => {
    if (!editor.value) return;
    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('Enter Link URL:', previousUrl);
    if (url === null) return;
    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const addImage = () => {
    if (!editor.value) return;
    const url = window.prompt('Enter Image URL:');
    if (url) {
        editor.value.chain().focus().setImage({ src: url }).run();
    }
};

const addMedia = () => {
    if (!editor.value) return;
    const url = window.prompt('Enter Video / Youtube URL:');
    if (url) {
        if (url.includes('youtube') || url.includes('youtu.be')) {
            editor.value.commands.setYoutubeVideo({ src: url });
        } else {
            editor.value.commands.insertContent(`<video controls src="${url}" class="max-w-full rounded-xl my-4"></video>`);
        }
    }
    activeMenu.value = null;
};

const insertTemplate = () => {
    if (!editor.value) return;
    const html = `
        <div class="my-4 rounded-xl bg-emerald-50/70 p-4 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800">
            <h4 class="text-base font-bold text-emerald-900 dark:text-emerald-300">💡 Pro Tip Template Header</h4>
            <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-1">Insert your key takeaways and highlights here...</p>
        </div>
    `;
    editor.value.commands.insertContent(html);
    activeMenu.value = null;
};

const insertTable = (rows = 3, cols = 3) => {
    if (!editor.value) return;
    editor.value.chain().focus().insertTable({ rows, cols, withHeaderRow: true }).run();
    activeMenu.value = null;
    activeInsertSubmenu.value = null;
    activeTableSubmenu.value = null;
    hoverGridRows.value = 0;
    hoverGridCols.value = 0;
};

const insertSpecialChar = (char: string) => {
    if (!editor.value) return;
    editor.value.chain().focus().insertContent(char).run();
    activeMenu.value = null;
    activeInsertSubmenu.value = null;
};

const insertPageBreak = () => {
    if (!editor.value) return;
    editor.value.chain().focus().insertContent('<hr class="my-8 border-t-2 border-dashed border-neutral-300 page-break" />').run();
    activeMenu.value = null;
};

const insertNbsp = () => {
    if (!editor.value) return;
    editor.value.chain().focus().insertContent('&nbsp;').run();
    activeMenu.value = null;
};

const insertAnchor = () => {
    if (!editor.value) return;
    const name = window.prompt('Enter Anchor ID (e.g. section-1):');
    if (name) {
        editor.value.chain().focus().insertContent(`<a id="${name}" class="scroll-mt-6"></a>`).run();
    }
    activeMenu.value = null;
};

const insertDate = (type: 'date' | 'time' | 'datetime') => {
    if (!editor.value) return;
    const now = new Date();
    let text = '';
    if (type === 'date') text = now.toLocaleDateString();
    else if (type === 'time') text = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    else text = `${now.toLocaleDateString()} ${now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;

    editor.value.chain().focus().insertContent(text).run();
    activeMenu.value = null;
    activeInsertSubmenu.value = null;
};

const handleCut = () => {
    document.execCommand('cut');
    activeMenu.value = null;
};

const handleCopy = () => {
    document.execCommand('copy');
    activeMenu.value = null;
};

const handlePaste = async () => {
    try {
        const text = await navigator.clipboard.readText();
        if (text && editor.value) {
            editor.value.chain().focus().insertContent(text).run();
        }
    } catch (e) {
        document.execCommand('paste');
    }
    activeMenu.value = null;
};

const handlePasteAsText = async () => {
    try {
        const text = await navigator.clipboard.readText();
        if (text && editor.value) {
            const cleanText = text.replace(/<[^>]*>/g, '');
            editor.value.chain().focus().insertContent(cleanText).run();
        }
    } catch (e) {
        console.warn(e);
    }
    activeMenu.value = null;
};

const handleSelectAll = () => {
    if (editor.value) {
        editor.value.chain().focus().selectAll().run();
    }
    activeMenu.value = null;
};

const executeReplaceAll = () => {
    if (!findText.value || !editor.value) return;
    const currentHtml = editor.value.getHTML();
    const regex = new RegExp(findText.value, 'gi');
    const newHtml = currentHtml.replace(regex, replaceText.value);
    editor.value.commands.setContent(newHtml, false);
    rawHtml.value = newHtml;
    emit('update:modelValue', newHtml);
    showFindReplaceModal.value = false;
};

const handlePrint = () => {
    activeMenu.value = null;
    const printWin = window.open('', '_blank');
    if (printWin) {
        printWin.document.write(`
            <html>
                <head>
                    <title>Print Blog Content</title>
                    <style>
                        body { font-family: system-ui, sans-serif; padding: 2rem; color: #111; line-height: 1.6; }
                        h1 { font-size: 2rem; font-weight: 800; }
                        h2 { font-size: 1.5rem; font-weight: 700; }
                        blockquote { border-left: 4px solid #10b981; padding-left: 1rem; italic; margin: 1rem 0; }
                    </style>
                </head>
                <body>
                    ${rawHtml.value}
                </body>
            </html>
        `);
        printWin.document.close();
        printWin.focus();
        setTimeout(() => {
            printWin.print();
            printWin.close();
        }, 250);
    }
};

const toggleCodeView = () => {
    if (!isCodeView.value) {
        if (editor.value) {
            rawHtml.value = editor.value.getHTML();
        }
        isCodeView.value = true;
    } else {
        isCodeView.value = false;
        if (editor.value) {
            editor.value.commands.setContent(rawHtml.value || '', false);
        }
    }
    activeMenu.value = null;
};

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    activeMenu.value = null;
};

const openWordCountModal = () => {
    showWordCountModal.value = true;
    activeMenu.value = null;
};

const openPreviewModal = () => {
    showPreviewModal.value = true;
    activeMenu.value = null;
};

const openFindReplaceModal = () => {
    showFindReplaceModal.value = true;
    activeMenu.value = null;
};

const openTablePropsModal = () => {
    showTablePropsModal.value = true;
    activeMenu.value = null;
};

const handleRawHtmlInput = () => {
    emit('update:modelValue', rawHtml.value);
    if (editor.value) {
        editor.value.commands.setContent(rawHtml.value || '', false);
    }
};

const clearContent = () => {
    if (confirm('Create new document? (This will clear current content)')) {
        if (editor.value) {
            editor.value.commands.setContent('', false);
        }
        rawHtml.value = '';
        emit('update:modelValue', '');
        activeMenu.value = null;
    }
};
</script>

<template>
    <div
        :class="[
            'relative font-sans text-neutral-800 dark:text-neutral-200 transition-all',
            isFullscreen
                ? 'fixed inset-0 z-[100] flex flex-col bg-white dark:bg-neutral-900 overflow-hidden'
                : 'rounded-lg border border-neutral-300 bg-white dark:border-neutral-800 dark:bg-neutral-900 shadow-xs'
        ]"
    >
        <!-- Transparent Backdrop to close active dropdown menu on click outside -->
        <div v-if="activeMenu" class="fixed inset-0 z-40" @click="activeMenu = null; activeFormatSubmenu = null; activeInsertSubmenu = null; activeTableSubmenu = null"></div>

        <!-- 1. TOP MENU BAR (File Edit View Insert Format Tools Table Help) -->
        <div class="relative z-50 flex flex-wrap items-center gap-3 rounded-t-lg border-b border-neutral-200 bg-white px-3 py-1 text-xs text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300 select-none">
            <!-- FILE MENU -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'file' ? null : 'file'"
                    class="rounded px-1.5 py-0.5 font-medium hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    File
                </button>

                <div
                    v-if="activeMenu === 'file'"
                    class="absolute left-0 top-full z-[100] mt-1 w-48 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200"
                >
                    <button
                        type="button"
                        @click="clearContent"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <File class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> New document
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="openPreviewModal"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Eye class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Preview
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="handlePrint"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Printer class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Print...
                    </button>
                </div>
            </div>

            <!-- EDIT MENU -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'edit' ? null : 'edit'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    Edit
                </button>

                <div
                    v-if="activeMenu === 'edit'"
                    class="absolute left-0 top-full z-[100] mt-1 w-56 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200"
                >
                    <button
                        type="button"
                        @click="editor?.chain().focus().undo().run(); activeMenu = null"
                        :disabled="!editor?.can().undo()"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 disabled:opacity-40 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Undo class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Undo
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+Z</span>
                    </button>

                    <button
                        type="button"
                        @click="editor?.chain().focus().redo().run(); activeMenu = null"
                        :disabled="!editor?.can().redo()"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 disabled:opacity-40 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Redo class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Redo
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+Y</span>
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="handleCut"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Scissors class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Cut
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+X</span>
                    </button>

                    <button
                        type="button"
                        @click="handleCopy"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Copy class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Copy
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+C</span>
                    </button>

                    <button
                        type="button"
                        @click="handlePaste"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Clipboard class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Paste
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+V</span>
                    </button>

                    <button
                        type="button"
                        @click="handlePasteAsText"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <ClipboardType class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Paste as text
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="handleSelectAll"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <BoxSelect class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Select all
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+A</span>
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="openFindReplaceModal"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Search class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Find and replace...
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+F</span>
                    </button>
                </div>
            </div>

            <!-- VIEW MENU -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'view' ? null : 'view'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    View
                </button>

                <div
                    v-if="activeMenu === 'view'"
                    class="absolute left-0 top-full z-[100] mt-1 w-56 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200"
                >
                    <button
                        type="button"
                        @click="toggleCodeView"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer font-mono"
                    >
                        <Code class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Source code
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="showVisualAids = !showVisualAids; activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="pl-6">Visual aids</span>
                        <Check v-if="showVisualAids" class="h-4 w-4 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <button
                        type="button"
                        @click="showInvisibleChars = !showInvisibleChars; activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Pilcrow class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Show invisible characters
                        </span>
                        <Check v-if="showInvisibleChars" class="h-4 w-4 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <button
                        type="button"
                        @click="showBlocks = !showBlocks; activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <span class="w-4 border border-dashed border-neutral-500 font-mono text-[10px] text-center">¶</span> Show blocks
                        </span>
                        <Check v-if="showBlocks" class="h-4 w-4 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @click="openPreviewModal"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Eye class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Preview
                    </button>

                    <button
                        type="button"
                        @click="toggleFullscreen"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Maximize v-if="!isFullscreen" class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            <Minimize v-else class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            Fullscreen
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+Shift+F</span>
                    </button>
                </div>
            </div>

            <!-- INSERT MENU -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'insert' ? null : 'insert'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    Insert
                </button>

                <div
                    v-if="activeMenu === 'insert'"
                    class="absolute left-0 top-full z-[100] mt-1 w-56 overflow-visible rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200 select-none"
                >
                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="addImage(); activeMenu = null"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <ImageIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Image...
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="setLink(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <LinkIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Link...
                        </span>
                        <span class="text-[10px] text-neutral-400">Ctrl+K</span>
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="addMedia"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Play class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Media...
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="insertTemplate"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Stamp class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Insert template...
                    </button>

                    <!-- Interactive 10x10 Table Grid in Insert Menu -->
                    <div class="relative group" @mouseenter="activeInsertSubmenu = 'table'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <TableIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Table
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <!-- 10x10 Grid Popover matching attached image -->
                        <div
                            v-if="activeInsertSubmenu === 'table'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[120]"
                        >
                            <div
                                class="rounded-md border border-neutral-300 bg-white p-2 shadow-xl dark:border-neutral-700 dark:bg-neutral-900"
                                @mouseleave="hoverGridRows = 0; hoverGridCols = 0"
                            >
                                <div class="inline-block border-t border-l border-neutral-300 dark:border-neutral-700">
                                    <div
                                        v-for="r in 10"
                                        :key="'r-' + r"
                                        class="flex"
                                    >
                                        <div
                                            v-for="c in 10"
                                            :key="'c-' + c"
                                            @mouseenter="hoverGridRows = r; hoverGridCols = c"
                                            @click="insertTable(r, c)"
                                            :class="[
                                                'h-4 w-4 border-r border-b border-neutral-300 dark:border-neutral-700 cursor-pointer transition-colors',
                                                r <= hoverGridRows && c <= hoverGridCols
                                                    ? 'bg-neutral-300 border-neutral-400 dark:bg-neutral-700'
                                                    : 'bg-white dark:bg-neutral-900'
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                                <div class="mt-2 border-t border-neutral-100 pt-1 text-center text-xs font-normal text-neutral-600 dark:border-neutral-800 dark:text-neutral-400">
                                    {{ hoverGridCols }}x{{ hoverGridRows }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <!-- Special Characters Submenu -->
                    <div class="relative group" @mouseenter="activeInsertSubmenu = 'char'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <span class="w-4 font-bold text-center text-sm">Ω</span> Special character...
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeInsertSubmenu === 'char'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[120]"
                        >
                            <div class="w-[180px] rounded-lg border border-neutral-200 bg-white p-2 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                                <div class="mb-1 text-[10px] font-bold text-neutral-400 tracking-wider uppercase">Characters</div>
                                <div class="grid grid-cols-4 gap-1 text-center font-semibold text-xs">
                                    <button
                                        v-for="char in specialCharacters"
                                        :key="char"
                                        type="button"
                                        @click="insertSpecialChar(char)"
                                        class="h-7 w-7 rounded border border-neutral-100 hover:border-neutral-300 bg-neutral-50 hover:bg-neutral-200 transition cursor-pointer flex items-center justify-center dark:bg-neutral-800 dark:border-neutral-700 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-100 font-serif text-sm"
                                    >
                                        {{ char }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="editor?.chain().focus().setHorizontalRule().run(); activeMenu = null"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Minus class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Horizontal line
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="insertPageBreak"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <SeparatorHorizontal class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Page break
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="insertNbsp"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Space class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Nonbreaking space
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeInsertSubmenu = null"
                        @click="insertAnchor"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <Bookmark class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Anchor...
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <div class="relative group" @mouseenter="activeInsertSubmenu = 'date'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <Clock class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Date/time
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeInsertSubmenu === 'date'"
                            class="absolute left-full bottom-0 -ml-1 pl-1 z-[110]"
                        >
                            <div class="w-44 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button type="button" @click="insertDate('date')" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Insert Date</button>
                                <button type="button" @click="insertDate('time')" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Insert Time</button>
                                <button type="button" @click="insertDate('datetime')" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Insert Date & Time</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORMAT MENU -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'format' ? null : 'format'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    Format
                </button>

                <div
                    v-if="activeMenu === 'format'"
                    class="absolute left-0 top-full z-[100] mt-1 w-56 overflow-visible rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200 select-none"
                >
                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleBold().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Bold class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Bold
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-neutral-400">Ctrl+B</span>
                            <Check v-if="editor?.isActive('bold')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                        </div>
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleItalic().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Italic class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Italic
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-neutral-400">Ctrl+I</span>
                            <Check v-if="editor?.isActive('italic')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                        </div>
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleUnderline().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <UnderlineIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Underline
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-neutral-400">Ctrl+U</span>
                            <Check v-if="editor?.isActive('underline')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                        </div>
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleStrike().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <Strikethrough class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Strikethrough
                        </span>
                        <Check v-if="editor?.isActive('strike')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleSuperscript().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <SuperscriptIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Superscript
                        </span>
                        <Check v-if="editor?.isActive('superscript')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleSubscript().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="flex items-center gap-2.5">
                            <SubscriptIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Subscript
                        </span>
                        <Check v-if="editor?.isActive('subscript')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().toggleCode().run(); activeMenu = null"
                        class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer font-mono"
                    >
                        <span class="flex items-center gap-2.5">
                            <Code class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Code
                        </span>
                        <Check v-if="editor?.isActive('code')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                    </button>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <!-- Blocks Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'blocks'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <Heading class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Blocks
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'blocks'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[110]"
                        >
                            <div class="w-44 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().setParagraph().run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Paragraph</span>
                                    <Check v-if="!editor?.isActive('heading') && !editor?.isActive('blockquote')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().toggleHeading({ level: 1 }).run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 font-bold hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Heading 1</span>
                                    <Check v-if="editor?.isActive('heading', { level: 1 })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 font-semibold hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Heading 2</span>
                                    <Check v-if="editor?.isActive('heading', { level: 2 })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Heading 3</span>
                                    <Check v-if="editor?.isActive('heading', { level: 3 })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().toggleBlockquote().run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 italic hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Blockquote</span>
                                    <Check v-if="editor?.isActive('blockquote')" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Fonts Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'fonts'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <Type class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Fonts
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'fonts'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[110]"
                        >
                            <div class="w-44 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().setFontFamily('Arial').run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 font-sans hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Arial</span>
                                    <Check v-if="editor?.isActive('textStyle', { fontFamily: 'Arial' })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().setFontFamily('Times New Roman').run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 font-serif hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Times New Roman</span>
                                    <Check v-if="editor?.isActive('textStyle', { fontFamily: 'Times New Roman' })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().setFontFamily('Georgia').run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 font-serif hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Georgia</span>
                                    <Check v-if="editor?.isActive('textStyle', { fontFamily: 'Georgia' })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                                <button
                                    type="button"
                                    @click="editor?.chain().focus().setFontFamily('Courier New').run(); activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 font-mono hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>Courier New</span>
                                    <Check v-if="editor?.isActive('textStyle', { fontFamily: 'Courier New' })" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Font Sizes Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'sizes'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <Baseline class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Font sizes
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'sizes'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[110]"
                        >
                            <div class="w-36 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button type="button" @click="(editor?.chain().focus() as any).setFontSize('12px').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">12pt</button>
                                <button type="button" @click="(editor?.chain().focus() as any).setFontSize('14px').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">14pt</button>
                                <button type="button" @click="(editor?.chain().focus() as any).setFontSize('18px').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">18pt</button>
                                <button type="button" @click="(editor?.chain().focus() as any).setFontSize('24px').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">24pt</button>
                                <button type="button" @click="(editor?.chain().focus() as any).setFontSize('36px').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">36pt</button>
                            </div>
                        </div>
                    </div>

                    <!-- Align Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'align'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <AlignLeft class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Align
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'align'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[110]"
                        >
                            <div class="w-36 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button type="button" @click="editor?.chain().focus().setTextAlign('left').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Left</button>
                                <button type="button" @click="editor?.chain().focus().setTextAlign('center').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Center</button>
                                <button type="button" @click="editor?.chain().focus().setTextAlign('right').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Right</button>
                                <button type="button" @click="editor?.chain().focus().setTextAlign('justify').run(); activeMenu = null" class="w-full rounded px-2.5 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">Justify</button>
                            </div>
                        </div>
                    </div>

                    <!-- Line Height Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'lineheight'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <ArrowUpDown class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Line height
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'lineheight'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[110]"
                        >
                            <div class="w-32 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button
                                    v-for="lh in ['1', '1.15', '1.5', '2']"
                                    :key="lh"
                                    type="button"
                                    @click="selectedLineHeight = lh; activeMenu = null"
                                    class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                >
                                    <span>{{ lh }}</span>
                                    <Check v-if="selectedLineHeight === lh" class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 font-bold" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <!-- Text Color Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'color'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <Palette class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Text color
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'color'"
                            class="absolute left-full bottom-0 -ml-1 pl-1 z-[120]"
                        >
                            <div class="w-[172px] rounded-lg border border-neutral-200 bg-white p-2 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                                <div class="grid grid-cols-5 gap-1">
                                    <button
                                        v-for="color in tinymceColorSwatches"
                                        :key="color"
                                        type="button"
                                        @click="setTextColor(color)"
                                        class="h-7 w-7 rounded border border-neutral-200/60 dark:border-neutral-700/60 cursor-pointer transition-transform hover:scale-110 shadow-xs"
                                        :style="{ backgroundColor: color }"
                                    ></button>
                                </div>

                                <div class="mt-1.5 pt-1.5 border-t border-neutral-100 dark:border-neutral-800 grid grid-cols-5 gap-1">
                                    <button
                                        type="button"
                                        @click="setTextColor('#000000')"
                                        class="h-7 w-7 rounded bg-black border border-neutral-300 dark:border-neutral-700 cursor-pointer transition-transform hover:scale-110"
                                        title="Black"
                                    ></button>

                                    <button
                                        type="button"
                                        @click="setTextColor('')"
                                        class="h-7 w-7 rounded border border-neutral-200 bg-neutral-50 flex items-center justify-center cursor-pointer transition-transform hover:scale-110 dark:bg-neutral-800 dark:border-neutral-700"
                                        title="Clear Color"
                                    >
                                        <Slash class="h-4 w-4 text-red-500 stroke-[2.5]" />
                                    </button>

                                    <div class="relative col-span-3 flex justify-end">
                                        <button
                                            type="button"
                                            @click="textColorInput?.click()"
                                            class="flex h-7 w-7 items-center justify-center rounded border border-neutral-200 bg-neutral-100 hover:bg-neutral-200 cursor-pointer transition-transform hover:scale-110 dark:bg-neutral-800 dark:border-neutral-700"
                                            title="Custom Color Palette Wheel"
                                        >
                                            <Palette class="h-4 w-4 text-neutral-700 dark:text-neutral-200" />
                                        </button>
                                        <input
                                            ref="textColorInput"
                                            type="color"
                                            @input="(e) => setTextColor((e.target as HTMLInputElement).value)"
                                            class="sr-only"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background Color Submenu -->
                    <div class="relative group" @mouseenter="activeFormatSubmenu = 'bg'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <Highlighter class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Background color
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeFormatSubmenu === 'bg'"
                            class="absolute left-full bottom-0 -ml-1 pl-1 z-[120]"
                        >
                            <div class="w-[172px] rounded-lg border border-neutral-200 bg-white p-2 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900">
                                <div class="grid grid-cols-5 gap-1">
                                    <button
                                        v-for="color in tinymceColorSwatches"
                                        :key="color"
                                        type="button"
                                        @click="setBgColor(color)"
                                        class="h-7 w-7 rounded border border-neutral-200/60 dark:border-neutral-700/60 cursor-pointer transition-transform hover:scale-110 shadow-xs"
                                        :style="{ backgroundColor: color }"
                                    ></button>
                                </div>

                                <div class="mt-1.5 pt-1.5 border-t border-neutral-100 dark:border-neutral-800 grid grid-cols-5 gap-1">
                                    <button
                                        type="button"
                                        @click="setBgColor('#000000')"
                                        class="h-7 w-7 rounded bg-black border border-neutral-300 dark:border-neutral-700 cursor-pointer transition-transform hover:scale-110"
                                        title="Black Highlight"
                                    ></button>

                                    <button
                                        type="button"
                                        @click="setBgColor('')"
                                        class="h-7 w-7 rounded border border-neutral-200 bg-neutral-50 flex items-center justify-center cursor-pointer transition-transform hover:scale-110 dark:bg-neutral-800 dark:border-neutral-700"
                                        title="Clear Highlight"
                                    >
                                        <Slash class="h-4 w-4 text-red-500 stroke-[2.5]" />
                                    </button>

                                    <div class="relative col-span-3 flex justify-end">
                                        <button
                                            type="button"
                                            @click="bgColorInput?.click()"
                                            class="flex h-7 w-7 items-center justify-center rounded border border-neutral-200 bg-neutral-100 hover:bg-neutral-200 cursor-pointer transition-transform hover:scale-110 dark:bg-neutral-800 dark:border-neutral-700"
                                            title="Custom Color Palette Wheel"
                                        >
                                            <Palette class="h-4 w-4 text-neutral-700 dark:text-neutral-200" />
                                        </button>
                                        <input
                                            ref="bgColorInput"
                                            type="color"
                                            @input="(e) => setBgColor((e.target as HTMLInputElement).value)"
                                            class="sr-only"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <!-- Clear formatting -->
                    <button
                        type="button"
                        @mouseenter="activeFormatSubmenu = null"
                        @click="editor?.chain().focus().unsetAllMarks().clearNodes().run(); activeMenu = null"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <RemoveFormatting class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Clear formatting
                    </button>
                </div>
            </div>

            <!-- TOOLS MENU -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'tools' ? null : 'tools'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    Tools
                </button>

                <div
                    v-if="activeMenu === 'tools'"
                    class="absolute left-0 top-full z-[100] mt-1 w-44 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200"
                >
                    <button
                        type="button"
                        @click="toggleCodeView"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer font-mono"
                    >
                        <Code class="h-4 w-4 text-neutral-600 dark:text-neutral-400 font-mono" /> Source code
                    </button>

                    <button
                        type="button"
                        @click="openWordCountModal"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <FileText class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Word count
                    </button>
                </div>
            </div>

            <!-- TABLE MENU (Matching Exact Attached Screenshots Design!) -->
            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'table' ? null : 'table'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    Table
                </button>

                <div
                    v-if="activeMenu === 'table'"
                    class="absolute left-0 top-full z-[100] mt-1 w-56 overflow-visible rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs text-neutral-800 dark:text-neutral-200 select-none"
                >
                    <!-- Table Option with 10x10 Interactive Grid Submenu -->
                    <div class="relative group" @mouseenter="activeTableSubmenu = 'table'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="flex items-center gap-2.5">
                                <TableIcon class="h-4 w-4 text-neutral-600 dark:text-neutral-400" /> Table
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <!-- 10x10 Grid Popover matching attached image -->
                        <div
                            v-if="activeTableSubmenu === 'table'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[120]"
                        >
                            <div
                                class="rounded-md border border-neutral-300 bg-white p-2 shadow-xl dark:border-neutral-700 dark:bg-neutral-900"
                                @mouseleave="hoverGridRows = 0; hoverGridCols = 0"
                            >
                                <div class="inline-block border-t border-l border-neutral-300 dark:border-neutral-700">
                                    <div
                                        v-for="r in 10"
                                        :key="'r-' + r"
                                        class="flex"
                                    >
                                        <div
                                            v-for="c in 10"
                                            :key="'c-' + c"
                                            @mouseenter="hoverGridRows = r; hoverGridCols = c"
                                            @click="insertTable(r, c)"
                                            :class="[
                                                'h-4 w-4 border-r border-b border-neutral-300 dark:border-neutral-700 cursor-pointer transition-colors',
                                                r <= hoverGridRows && c <= hoverGridCols
                                                    ? 'bg-neutral-300 border-neutral-400 dark:bg-neutral-700'
                                                    : 'bg-white dark:bg-neutral-900'
                                            ]"
                                        ></div>
                                    </div>
                                </div>
                                <div class="mt-2 border-t border-neutral-100 pt-1 text-center text-xs font-normal text-neutral-600 dark:border-neutral-800 dark:text-neutral-400">
                                    {{ hoverGridCols }}x{{ hoverGridRows }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cell Submenu (Matching Screenshot 1) -->
                    <div class="relative group" @mouseenter="activeTableSubmenu = 'cell'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="pl-6">Cell</span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeTableSubmenu === 'cell'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[120]"
                        >
                            <div class="w-48 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button type="button" @click="showCellPropsModal = true; activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Grid class="h-4 w-4 text-neutral-500" /> Cell properties
                                </button>
                                <button type="button" @click="editor?.chain().focus().mergeCells().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <TableIcon class="h-4 w-4 text-neutral-500" /> Merge cells
                                </button>
                                <button type="button" @click="editor?.chain().focus().splitCell().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <SquareX class="h-4 w-4 text-neutral-500" /> Split cell
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Row Submenu (Matching Screenshot 2) -->
                    <div class="relative group" @mouseenter="activeTableSubmenu = 'row'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="pl-6">Row</span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeTableSubmenu === 'row'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[120]"
                        >
                            <div class="w-52 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button type="button" @click="editor?.chain().focus().addRowBefore().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Rows class="h-4 w-4 text-neutral-500" /> Insert row before
                                </button>
                                <button type="button" @click="editor?.chain().focus().addRowAfter().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Rows class="h-4 w-4 text-neutral-500" /> Insert row after
                                </button>
                                <button type="button" @click="editor?.chain().focus().deleteRow().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer text-neutral-700 dark:text-neutral-300">
                                    <SquareX class="h-4 w-4 text-neutral-500" /> Delete row
                                </button>
                                <button type="button" @click="showRowPropsModal = true; activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Settings2 class="h-4 w-4 text-neutral-500" /> Row properties
                                </button>

                                <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                                <button type="button" @click="handleCut" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Scissors class="h-4 w-4 text-neutral-500" /> Cut row
                                </button>
                                <button type="button" @click="handleCopy" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Copy class="h-4 w-4 text-neutral-500" /> Copy row
                                </button>
                                <button type="button" @click="editor?.chain().focus().addRowBefore().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Clipboard class="h-4 w-4 text-neutral-500" /> Paste row before
                                </button>
                                <button type="button" @click="editor?.chain().focus().addRowAfter().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Clipboard class="h-4 w-4 text-neutral-500" /> Paste row after
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Column Submenu (Matching Screenshot 3) -->
                    <div class="relative group" @mouseenter="activeTableSubmenu = 'column'">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                        >
                            <span class="pl-6">Column</span>
                            <ChevronRight class="h-3.5 w-3.5 text-neutral-400" />
                        </button>

                        <div
                            v-if="activeTableSubmenu === 'column'"
                            class="absolute left-full top-0 -ml-1 pl-1 z-[120]"
                        >
                            <div class="w-56 rounded-md border border-neutral-200 bg-white p-1 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-xs">
                                <button type="button" @click="editor?.chain().focus().addColumnBefore().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Columns class="h-4 w-4 text-neutral-500" /> Insert column before
                                </button>
                                <button type="button" @click="editor?.chain().focus().addColumnAfter().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Columns class="h-4 w-4 text-neutral-500" /> Insert column after
                                </button>
                                <button type="button" @click="editor?.chain().focus().deleteColumn().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <SquareX class="h-4 w-4 text-neutral-500" /> Delete column
                                </button>

                                <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                                <button type="button" @click="handleCut" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Scissors class="h-4 w-4 text-neutral-500" /> Cut column
                                </button>
                                <button type="button" @click="handleCopy" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Copy class="h-4 w-4 text-neutral-500" /> Copy column
                                </button>
                                <button type="button" @click="editor?.chain().focus().addColumnBefore().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Clipboard class="h-4 w-4 text-neutral-500" /> Paste column before
                                </button>
                                <button type="button" @click="editor?.chain().focus().addColumnAfter().run(); activeMenu = null" class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                                    <Clipboard class="h-4 w-4 text-neutral-500" /> Paste column after
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-1 border-neutral-200 dark:border-neutral-800" />

                    <!-- Table properties -->
                    <button
                        type="button"
                        @mouseenter="activeTableSubmenu = null"
                        @click="openTablePropsModal"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-neutral-400 dark:text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        <span class="pl-6">Table properties</span>
                    </button>

                    <!-- Delete Table -->
                    <button
                        type="button"
                        @mouseenter="activeTableSubmenu = null"
                        @click="editor?.chain().focus().deleteTable().run(); activeMenu = null"
                        class="flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer text-neutral-600 dark:text-neutral-400"
                    >
                        <SquareX class="h-4 w-4 text-neutral-500" /> Delete table
                    </button>
                </div>
            </div>

            <div class="relative">
                <button
                    type="button"
                    @click="activeMenu = activeMenu === 'help' ? null : 'help'"
                    class="rounded px-1.5 py-0.5 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    Help
                </button>
            </div>
        </div>

        <!-- 2. MAIN TOOLBAR (With Format Block, Font Style/Family & Font Size Selectors) -->
        <div class="relative z-20 flex flex-wrap items-center border-b border-neutral-200 bg-white text-neutral-700 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200">
            <div v-if="editor" class="flex flex-wrap items-center">
                <!-- Group 1: Undo & Redo -->
                <div class="flex items-center px-1.5 py-1 border-r border-neutral-200 dark:border-neutral-800">
                    <button
                        type="button"
                        @click="editor.chain().focus().undo().run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 disabled:opacity-30 cursor-pointer"
                        title="Undo"
                        :disabled="isCodeView || !editor.can().undo()"
                    >
                        <Undo class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().redo().run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 disabled:opacity-30 cursor-pointer"
                        title="Redo"
                        :disabled="isCodeView || !editor.can().redo()"
                    >
                        <Redo class="h-4 w-4" />
                    </button>
                </div>

                <!-- Group 2: Format Block, Font Style/Family & Font Size Selectors -->
                <div class="flex items-center gap-1 px-2 py-1 border-r border-neutral-200 dark:border-neutral-800">
                    <!-- Heading Select -->
                    <div class="relative flex items-center">
                        <select
                            :value="editor.isActive('heading', { level: 1 }) ? 'h1' : editor.isActive('heading', { level: 2 }) ? 'h2' : editor.isActive('heading', { level: 3 }) ? 'h3' : 'p'"
                            @change="handleHeadingChange"
                            class="h-7 w-28 appearance-none bg-transparent pl-2 pr-5 text-xs text-neutral-700 outline-none hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer"
                            :disabled="isCodeView"
                        >
                            <option value="p">Paragraph</option>
                            <option value="h1">Heading 1</option>
                            <option value="h2">Heading 2</option>
                            <option value="h3">Heading 3</option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-1 h-3.5 w-3.5 text-neutral-400" />
                    </div>

                    <div class="h-4 w-px bg-neutral-200 dark:bg-neutral-800 mx-0.5"></div>

                    <!-- Font Style / Font Family Select -->
                    <div class="relative flex items-center">
                        <select
                            @change="handleFontFamilyChange"
                            class="h-7 w-28 appearance-none bg-transparent pl-2 pr-5 text-xs text-neutral-700 outline-none hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer"
                            :disabled="isCodeView"
                        >
                            <option value="default">Font Style</option>
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Impact">Impact</option>
                            <option value="Trebuchet MS">Trebuchet MS</option>
                            <option value="Verdana">Verdana</option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-1 h-3.5 w-3.5 text-neutral-400" />
                    </div>

                    <div class="h-4 w-px bg-neutral-200 dark:bg-neutral-800 mx-0.5"></div>

                    <!-- Font Size Select -->
                    <div class="relative flex items-center">
                        <select
                            @change="handleFontSizeChange"
                            class="h-7 w-20 appearance-none bg-transparent pl-2 pr-5 text-xs text-neutral-700 outline-none hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-800 cursor-pointer"
                            :disabled="isCodeView"
                        >
                            <option value="default">Size</option>
                            <option value="12px">12px</option>
                            <option value="14px">14px</option>
                            <option value="16px">16px</option>
                            <option value="18px">18px</option>
                            <option value="20px">20px</option>
                            <option value="24px">24px</option>
                            <option value="32px">32px</option>
                            <option value="40px">40px</option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-1 h-3.5 w-3.5 text-neutral-400" />
                    </div>
                </div>

                <!-- Group 3: Bold (B), Italic (I), Superscript & Subscript -->
                <div class="flex items-center px-1.5 py-1 border-r border-neutral-200 dark:border-neutral-800">
                    <button
                        type="button"
                        @click="editor.chain().focus().toggleBold().run()"
                        :class="[
                            'flex h-7 w-7 items-center justify-center rounded font-serif font-black text-sm cursor-pointer',
                            editor.isActive('bold')
                                ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                : 'hover:bg-neutral-100 text-neutral-700 dark:hover:bg-neutral-800 dark:text-neutral-300'
                        ]"
                        title="Bold"
                        :disabled="isCodeView"
                    >
                        B
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().toggleItalic().run()"
                        :class="[
                            'flex h-7 w-7 items-center justify-center rounded font-serif italic text-sm font-bold cursor-pointer',
                            editor.isActive('italic')
                                ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                : 'hover:bg-neutral-100 text-neutral-700 dark:hover:bg-neutral-800 dark:text-neutral-300'
                        ]"
                        title="Italic"
                        :disabled="isCodeView"
                    >
                        I
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().toggleSuperscript().run()"
                        :class="[
                            'flex h-7 w-7 items-center justify-center rounded cursor-pointer',
                            editor.isActive('superscript')
                                ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                : 'hover:bg-neutral-100 text-neutral-700 dark:hover:bg-neutral-800 dark:text-neutral-300'
                        ]"
                        title="Superscript (x²)"
                        :disabled="isCodeView"
                    >
                        <SuperscriptIcon class="h-3.5 w-3.5" />
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().toggleSubscript().run()"
                        :class="[
                            'flex h-7 w-7 items-center justify-center rounded cursor-pointer',
                            editor.isActive('subscript')
                                ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                : 'hover:bg-neutral-100 text-neutral-700 dark:hover:bg-neutral-800 dark:text-neutral-300'
                        ]"
                        title="Subscript (x₂)"
                        :disabled="isCodeView"
                    >
                        <SubscriptIcon class="h-3.5 w-3.5" />
                    </button>
                </div>

                <!-- Group 4: Alignment Icons -->
                <div class="flex items-center px-1.5 py-1 border-r border-neutral-200 dark:border-neutral-800">
                    <button
                        type="button"
                        @click="editor.chain().focus().setTextAlign('left').run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Align Left"
                        :disabled="isCodeView"
                    >
                        <AlignLeft class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().setTextAlign('center').run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Align Center"
                        :disabled="isCodeView"
                    >
                        <AlignCenter class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().setTextAlign('right').run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Align Right"
                        :disabled="isCodeView"
                    >
                        <AlignRight class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().setTextAlign('justify').run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Justify"
                        :disabled="isCodeView"
                    >
                        <AlignJustify class="h-4 w-4" />
                    </button>
                </div>

                <!-- Group 5: Bullet List, Numbered List, Outdent, Indent -->
                <div class="flex items-center px-1.5 py-1 border-r border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleBulletList().run()"
                            :class="[
                                'flex h-7 w-7 items-center justify-center rounded cursor-pointer',
                                editor.isActive('bulletList')
                                    ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                    : 'hover:bg-neutral-100 text-neutral-600 dark:hover:bg-neutral-800 dark:text-neutral-400'
                            ]"
                            title="Bulleted List"
                            :disabled="isCodeView"
                        >
                            <List class="h-4 w-4" />
                        </button>
                        <ChevronDown class="h-3 w-3 text-neutral-400 -ml-1 mr-1" />
                    </div>

                    <div class="flex items-center">
                        <button
                            type="button"
                            @click="editor.chain().focus().toggleOrderedList().run()"
                            :class="[
                                'flex h-7 w-7 items-center justify-center rounded cursor-pointer',
                                editor.isActive('orderedList')
                                    ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                    : 'hover:bg-neutral-100 text-neutral-600 dark:hover:bg-neutral-800 dark:text-neutral-400'
                            ]"
                            title="Numbered List"
                            :disabled="isCodeView"
                        >
                            <ListOrdered class="h-4 w-4" />
                        </button>
                        <ChevronDown class="h-3 w-3 text-neutral-400 -ml-1 mr-1" />
                    </div>

                    <button
                        type="button"
                        @click="editor.chain().focus().liftListItem('listItem').run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Decrease Indent"
                        :disabled="isCodeView"
                    >
                        <Outdent class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="editor.chain().focus().sinkListItem('listItem').run()"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Increase Indent"
                        :disabled="isCodeView"
                    >
                        <Indent class="h-4 w-4" />
                    </button>
                </div>

                <!-- Group 6: Link & Image -->
                <div class="flex items-center px-1.5 py-1 border-r border-neutral-200 dark:border-neutral-800">
                    <button
                        type="button"
                        @click="setLink"
                        :class="[
                            'flex h-7 w-7 items-center justify-center rounded cursor-pointer',
                            editor.isActive('link')
                                ? 'bg-neutral-200 text-neutral-900 dark:bg-neutral-700 dark:text-white'
                                : 'hover:bg-neutral-100 text-neutral-600 dark:hover:bg-neutral-800 dark:text-neutral-400'
                        ]"
                        title="Insert Link"
                        :disabled="isCodeView"
                    >
                        <LinkIcon class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        @click="addImage"
                        class="flex h-7 w-7 items-center justify-center rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-pointer"
                        title="Insert Image"
                        :disabled="isCodeView"
                    >
                        <ImageIcon class="h-4 w-4" />
                    </button>
                </div>

                <!-- Group 7: More Options (...) -->
                <div class="flex items-center px-1.5 py-1">
                    <button
                        type="button"
                        @click="toggleCodeView"
                        :class="[
                            'flex h-7 w-7 items-center justify-center rounded cursor-pointer',
                            isCodeView
                                ? 'bg-neutral-800 text-white dark:bg-emerald-600'
                                : 'hover:bg-neutral-100 text-neutral-600 dark:hover:bg-neutral-800 dark:text-neutral-400'
                        ]"
                        title="Toggle HTML Source"
                    >
                        <MoreHorizontal class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. EDITOR CONTENT AREA (Clean white writing area directly under toolbar) -->
        <div class="relative flex-1 bg-white p-4 dark:bg-neutral-900 z-10">
            <EditorContent
                v-show="!isCodeView"
                :editor="editor"
                :class="[
                    'min-h-[260px] text-sm leading-relaxed text-neutral-900 outline-none dark:text-neutral-100 [&_.ProseMirror]:min-h-[240px] [&_.ProseMirror]:outline-none [&_.ProseMirror_h1]:text-2xl [&_.ProseMirror_h1]:font-extrabold [&_.ProseMirror_h1]:my-3 [&_.ProseMirror_h2]:text-xl [&_.ProseMirror_h2]:font-bold [&_.ProseMirror_h2]:my-2 [&_.ProseMirror_h3]:text-lg [&_.ProseMirror_h3]:font-semibold [&_.ProseMirror_p]:my-1.5 [&_.ProseMirror_ul]:list-disc [&_.ProseMirror_ul]:pl-5 [&_.ProseMirror_ol]:list-decimal [&_.ProseMirror_ol]:pl-5 [&_.ProseMirror_blockquote]:border-l-4 [&_.ProseMirror_blockquote]:border-neutral-300 [&_.ProseMirror_blockquote]:pl-3 [&_.ProseMirror_a]:text-blue-600 [&_.ProseMirror_a]:underline',
                    showBlocks ? '[&_.ProseMirror_p]:border [&_.ProseMirror_p]:border-dashed [&_.ProseMirror_p]:border-neutral-300 [&_.ProseMirror_h1]:border [&_.ProseMirror_h1]:border-dashed [&_.ProseMirror_h1]:border-neutral-300 [&_.ProseMirror_h2]:border [&_.ProseMirror_h2]:border-dashed [&_.ProseMirror_h2]:border-neutral-300' : ''
                ]"
            />

            <textarea
                v-show="isCodeView"
                v-model="rawHtml"
                @input="handleRawHtmlInput"
                rows="14"
                class="w-full font-mono text-xs leading-relaxed p-3 bg-neutral-950 text-emerald-400 outline-none rounded"
                placeholder="<p>Write HTML code...</p>"
            />
        </div>

        <!-- 4. BOTTOM STATUS BAR (Matching Screenshot: Uppercase P, 0 WORDS, POWERED BY STOREMINT //) -->
        <div class="relative z-20 flex items-center justify-between rounded-b-lg border-t border-neutral-200 bg-white px-3 py-1.5 text-[11px] font-medium uppercase tracking-wide text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-400 select-none">
            <div>
                <span>{{ activeTagPath }}</span>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="openWordCountModal" class="hover:underline cursor-pointer">
                    {{ wordCount }} WORDS
                </button>
                <span class="text-neutral-400 font-semibold">POWERED BY STOREMINT</span>
                <div class="flex flex-col gap-0.5 opacity-40">
                    <div class="h-0.5 w-2 bg-neutral-400"></div>
                    <div class="h-0.5 w-1 bg-neutral-400 ml-1"></div>
                </div>
            </div>
        </div>

        <!-- TABLE PROPERTIES MODAL DIALOG -->
        <div
            v-if="showTablePropsModal"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-neutral-900/40 backdrop-blur-xs p-4"
        >
            <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-5 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <Settings2 class="h-4 w-4 text-emerald-600" />
                        <h3 class="text-sm font-bold">Table Properties</h3>
                    </div>
                    <button type="button" @click="showTablePropsModal = false" class="rounded p-1 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Width</label>
                        <input
                            type="text"
                            value="100%"
                            disabled
                            class="w-full rounded border border-neutral-300 bg-neutral-100 px-3 py-1.5 text-xs text-neutral-600 outline-none dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400 cursor-not-allowed"
                        />
                    </div>
                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Cell padding</label>
                        <input
                            type="text"
                            value="8px"
                            disabled
                            class="w-full rounded border border-neutral-300 bg-neutral-100 px-3 py-1.5 text-xs text-neutral-600 outline-none dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400 cursor-not-allowed"
                        />
                    </div>
                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Cell spacing</label>
                        <input
                            type="text"
                            value="0px"
                            disabled
                            class="w-full rounded border border-neutral-300 bg-neutral-100 px-3 py-1.5 text-xs text-neutral-600 outline-none dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-400 cursor-not-allowed"
                        />
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        @click="showTablePropsModal = false"
                        class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 cursor-pointer"
                    >
                        Save & Close
                    </button>
                </div>
            </div>
        </div>

        <!-- CELL PROPERTIES MODAL DIALOG -->
        <div
            v-if="showCellPropsModal"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-neutral-900/40 backdrop-blur-xs p-4"
        >
            <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-5 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <Grid class="h-4 w-4 text-emerald-600" />
                        <h3 class="text-sm font-bold">Cell Properties</h3>
                    </div>
                    <button type="button" @click="showCellPropsModal = false" class="rounded p-1 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Cell type</label>
                        <select class="w-full rounded border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-800 outline-none dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                            <option value="td">Data cell</option>
                            <option value="th">Header cell</option>
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        @click="showCellPropsModal = false"
                        class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 cursor-pointer"
                    >
                        Save & Close
                    </button>
                </div>
            </div>
        </div>

        <!-- ROW PROPERTIES MODAL DIALOG -->
        <div
            v-if="showRowPropsModal"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-neutral-900/40 backdrop-blur-xs p-4"
        >
            <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-5 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <Settings2 class="h-4 w-4 text-emerald-600" />
                        <h3 class="text-sm font-bold">Row Properties</h3>
                    </div>
                    <button type="button" @click="showRowPropsModal = false" class="rounded p-1 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Row type</label>
                        <select class="w-full rounded border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-800 outline-none dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                            <option value="body">Body row</option>
                            <option value="header">Header row</option>
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        @click="showRowPropsModal = false"
                        class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 cursor-pointer"
                    >
                        Save & Close
                    </button>
                </div>
            </div>
        </div>

        <!-- FIND AND REPLACE MODAL DIALOG -->
        <div
            v-if="showFindReplaceModal"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-neutral-900/40 backdrop-blur-xs p-4"
        >
            <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-5 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-emerald-600" />
                        <h3 class="text-sm font-bold">Find and Replace</h3>
                    </div>
                    <button type="button" @click="showFindReplaceModal = false" class="rounded p-1 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Find text</label>
                        <input
                            v-model="findText"
                            type="text"
                            placeholder="Enter text to find..."
                            class="w-full rounded border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-800 outline-none focus:border-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                        />
                    </div>

                    <div>
                        <label class="block text-neutral-500 font-medium mb-1">Replace with</label>
                        <input
                            v-model="replaceText"
                            type="text"
                            placeholder="Enter replacement text..."
                            class="w-full rounded border border-neutral-300 bg-white px-3 py-1.5 text-xs text-neutral-800 outline-none focus:border-emerald-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                        />
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="showFindReplaceModal = false"
                        class="rounded-lg border border-neutral-300 px-3 py-1.5 text-xs font-semibold text-neutral-700 hover:bg-neutral-100 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800 cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="executeReplaceAll"
                        class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 cursor-pointer"
                    >
                        Replace All
                    </button>
                </div>
            </div>
        </div>

        <!-- WORD COUNT MODAL DIALOG -->
        <div
            v-if="showWordCountModal"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-neutral-900/40 backdrop-blur-xs p-4"
        >
            <div class="w-full max-w-xs rounded-xl border border-neutral-200 bg-white p-5 shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200">
                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 dark:border-neutral-800">
                    <h3 class="text-sm font-bold">Word Count Statistics</h3>
                    <button type="button" @click="showWordCountModal = false" class="rounded p-1 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-2.5 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Words:</span>
                        <span class="font-bold font-mono">{{ wordCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Characters (with spaces):</span>
                        <span class="font-bold font-mono">{{ charCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Characters (no spaces):</span>
                        <span class="font-bold font-mono">{{ charCountNoSpaces }}</span>
                    </div>
                    <div class="flex justify-between border-t border-neutral-100 pt-2 dark:border-neutral-800">
                        <span class="text-neutral-500 dark:text-neutral-400">Paragraphs:</span>
                        <span class="font-bold font-mono">{{ paragraphCount }}</span>
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button
                        type="button"
                        @click="showWordCountModal = false"
                        class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- ARTICLE PREVIEW MODAL DIALOG -->
        <div
            v-if="showPreviewModal"
            class="fixed inset-0 z-[200] flex items-center justify-center bg-neutral-900/50 backdrop-blur-xs p-4 sm:p-6"
        >
            <div class="flex max-h-[85vh] w-full max-w-3xl flex-col rounded-2xl border border-neutral-200 bg-white shadow-2xl dark:border-neutral-800 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200">
                <div class="flex items-center justify-between border-b border-neutral-100 p-4 dark:border-neutral-800">
                    <div class="flex items-center gap-2">
                        <Eye class="h-4 w-4 text-emerald-600" />
                        <h3 class="text-sm font-extrabold">Article Live Preview</h3>
                    </div>
                    <button type="button" @click="showPreviewModal = false" class="rounded p-1 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="overflow-y-auto p-6 text-sm leading-relaxed space-y-4 font-normal [&_h1]:text-2xl [&_h1]:font-extrabold [&_h2]:text-xl [&_h2]:font-bold [&_h3]:text-lg [&_h3]:font-semibold [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_blockquote]:border-l-4 [&_blockquote]:border-emerald-500 [&_blockquote]:pl-4 [&_blockquote]:italic [&_a]:text-emerald-600 [&_a]:underline" v-html="rawHtml"></div>

                <div class="flex justify-end border-t border-neutral-100 p-3 dark:border-neutral-800">
                    <button
                        type="button"
                        @click="showPreviewModal = false"
                        class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-700 cursor-pointer"
                    >
                        Close Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
