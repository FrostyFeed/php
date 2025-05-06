<?php
   use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
   use Symfony\Component\HttpFoundation\JsonResponse;
   use Symfony\Component\HttpFoundation\Request;
   use Symfony\Component\HttpFoundation\Response;
   use Symfony\Component\Routing\Annotation\Route; 
   
   #[Route('/api/demo-items')] 
   class DemoItemController extends AbstractController
   {
       private static array $items = [];
       private static int $nextId = 1; 
   
       
       #[Route('', name: 'api_demo_item_index', methods: ['GET'])]
       public function index(): JsonResponse
       {
           return $this->json(array_values(self::$items));
       }
   
       #[Route('', name: 'api_demo_item_store', methods: ['POST'])]
       public function store(Request $request): JsonResponse
       {
           $data = json_decode($request->getContent(), true);
   
           if (empty($data['name'])) {
               return $this->json(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
           }
   
           $id = self::$nextId++;
           $newItem = [
               'id' => $id,
               'name' => $data['name'],
               'description' => $data['description'] ?? null,
               'created_at' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
               'updated_at' => (new \DateTime())->format(\DateTimeInterface::ISO8601),
           ];
   
           self::$items[$id] = $newItem;
   
           return $this->json($newItem, Response::HTTP_CREATED);
       }
   
    
       #[Route('/{id<\d+>}', name: 'api_demo_item_show', methods: ['GET'])]
       public function show(int $id): JsonResponse
       {
           if (!isset(self::$items[$id])) {
               return $this->json(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
           }
           return $this->json(self::$items[$id]);
       }
   
 
       #[Route('/{id<\d+>}', name: 'api_demo_item_update', methods: ['PUT', 'PATCH'])]
       public function update(Request $request, int $id): JsonResponse
       {
           if (!isset(self::$items[$id])) {
               return $this->json(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
           }
   
           $data = json_decode($request->getContent(), true);
           $item = &self::$items[$id]; 
   
           if (isset($data['name'])) {
               if (empty($data['name'])) {
                    return $this->json(['message' => 'Name cannot be empty if provided'], Response::HTTP_BAD_REQUEST);
               }
               $item['name'] = $data['name'];
           }
           if (array_key_exists('description', $data)) { 
               $item['description'] = $data['description'];
           }
           $item['updated_at'] = (new \DateTime())->format(\DateTimeInterface::ISO8601);
   
           return $this->json($item);
       }
   
   
       #[Route('/{id<\d+>}', name: 'api_demo_item_destroy', methods: ['DELETE'])]
       public function destroy(int $id): JsonResponse
       {
           if (!isset(self::$items[$id])) {
               return $this->json(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
           }
   
           unset(self::$items[$id]);
   
           return $this->json(['message' => 'Item deleted successfully'], Response::HTTP_OK);
           ponse(null, Response::HTTP_NO_CONTENT);
       }
   }